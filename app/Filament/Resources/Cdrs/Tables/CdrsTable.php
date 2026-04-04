<?php

namespace App\Filament\Resources\Cdrs\Tables;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Filament\Exports\CdrsExporter;
use App\Models\Cdr;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Jobs\CreateXlsxFile;
use Filament\Actions\Exports\Jobs\ExportCompletion;
use Filament\Actions\Exports\Jobs\PrepareCsvExport;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CdrsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('calldate', 'desc')
            ->selectable()
            ->selectCurrentPageOnly(false)
            ->columns([
                TextColumn::make('calldate')
                    ->label('Call Date & Time')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('src')
                    ->label('Source')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('dst')
                    ->label('Destination')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('extension')
                    ->label('Extension')
                    ->getStateUsing(function (Cdr $record): ?string {
                        return $record->extension;
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('direction')
                    ->label('Direction')
                    ->getStateUsing(function (Cdr $record): string {
                        return $record->direction;
                    })
                    ->badge()
                    ->colors([
                        'success' => 'In',
                        'danger' => 'Out',
                    ])
                    ->sortable(),

                TextColumn::make('billsec_duration')
                    ->label('Duration')
                    ->getStateUsing(function (Cdr $record): string {
                        return $record->bill_sec_duration;
                    }),

                TextColumn::make('disposition')
                    ->label('Disposition')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->colors([
                        'success' => 'ANSWERED',
                        'danger' => 'FAILED',
                        'warning' => 'BUSY',
                        'info' => 'NO ANSWER',
                    ]),

                ViewColumn::make('recording')
                    ->label('Recording')
                    ->view('filament.tables.columns.recording-player')
                    ->getStateUsing(function (Cdr $record): array {
                        $filepath = 'monitor_1/'.$record->uniqueid.'.wav';

                        // Cache file existence check for 1 hour per uniqueid to reduce I/O
                        $hasFile = Cache::remember(
                            'recording_exists_'.$record->uniqueid,
                            3600,
                            fn () => Storage::disk('public')->exists($filepath) || file_exists(storage_path('app/public/'.$filepath))
                        );

                        return [
                            'uniqueid' => $record->uniqueid,
                            'hasFile' => $hasFile,
                            'filepath' => $filepath,
                            'url' => $hasFile ? asset('storage/'.$filepath) : null,
                        ];
                    }),

                TextColumn::make('dcontext')
                    ->label('Context')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('From Date')
                            ->default(now()->subDays(30)),
                        DatePicker::make('to_date')
                            ->label('To Date')
                            ->default(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $q) => $q->whereDate('calldate', '>=', $data['from_date'])
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $q) => $q->whereDate('calldate', '<=', $data['to_date'])
                            );
                    }),

                SelectFilter::make('disposition')
                    ->options(function (): array {
                        // Cache disposition options for 24 hours to avoid full table scan
                        return Cache::remember(
                            'cdr_disposition_options',
                            60 * 60 * 24,
                            fn () => Cdr::distinct('disposition')
                                ->pluck('disposition', 'disposition')
                                ->toArray()
                        );
                    }),

                // TernaryFilter::make('is_internal')
                //     ->label('Call Type')
                //     ->queries(
                //         true: fn (Builder $query) => $query->whereRaw('CHAR_LENGTH(src) = 9'),
                //         false: fn (Builder $query) => $query->whereRaw('CHAR_LENGTH(src) != 9')
                //     )
                //     ->attribute('src'),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (BulkAction $action): void {
                            $records = $action->getSelectedRecords();

                            // Get column map
                            $columnMap = collect(CdrsExporter::getColumns())
                                ->mapWithKeys(fn (ExportColumn $column) => [
                                    $column->getName() => $column->getLabel(),
                                ])
                                ->all();

                            // Create export record
                            $export = Export::make([
                                'user_id' => auth()->id(),
                                'exporter' => CdrsExporter::class,
                                'total_rows' => $records->count(),
                            ]);

                            $exporter = $export->getExporter(columnMap: $columnMap, options: []);
                            $export->file_disk = $exporter->getFileDisk();
                            $export->file_name = $exporter->getFileName($export);
                            $export->save();

                            // Dispatch export job using Filament's internal pattern
                            $columnMap = collect(CdrsExporter::getColumns())
                                ->mapWithKeys(fn (ExportColumn $column) => [
                                    $column->getName() => $column->getLabel(),
                                ])
                                ->all();

                            // Get the query for selected records only
                            $query = $records->toQuery();
                            $serializedQuery = EloquentSerializeFacade::serialize($query);

                            $formats = [ExportFormat::Csv, ExportFormat::Xlsx];
                            $hasXlsx = in_array(ExportFormat::Xlsx, $formats);
                            $hasCsv = in_array(ExportFormat::Csv, $formats);

                            $makeCreateXlsxFileJob = fn () => new CreateXlsxFile(
                                export: $export,
                                columnMap: $columnMap,
                            );

                            $jobs = [
                                Bus::batch([
                                    app(PrepareCsvExport::class, [
                                        'export' => $export,
                                        'query' => $serializedQuery,
                                        'columnMap' => $columnMap,
                                        'options' => [],
                                        'chunkSize' => 100,
                                    ]),
                                ])->allowFailures(),
                            ];

                            // Add CreateXlsxFile before completion if only XLSX
                            if ($hasXlsx && ! $hasCsv) {
                                $jobs[] = $makeCreateXlsxFileJob();
                            }

                            // Add ExportCompletion
                            $jobs[] = app(ExportCompletion::class, [
                                'authGuard' => 'web',
                                'export' => $export,
                                'columnMap' => $columnMap,
                                'formats' => $formats,
                                'options' => [],
                            ]);

                            // Add CreateXlsxFile after completion if both CSV and XLSX
                            if ($hasXlsx && $hasCsv) {
                                $jobs[] = $makeCreateXlsxFileJob();
                            }

                            Bus::chain($jobs)->dispatch();

                            Notification::make()
                                ->title('Export Started')
                                ->body('Exporting '.$records->count().' selected record(s)...')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
