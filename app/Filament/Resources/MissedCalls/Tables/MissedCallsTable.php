<?php

namespace App\Filament\Resources\MissedCalls\Tables;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Filament\Exports\AbandonedCallExporter;
use App\Models\AbandonedNew;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Jobs\CreateXlsxFile;
use Filament\Actions\Exports\Jobs\ExportCompletion;
use Filament\Actions\Exports\Jobs\PrepareCsvExport;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DateTimePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;

class MissedCallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                AbandonedNew::query()
                    ->whereIn('status', ['CHANUNAVAIL', 'NOANSWER', 'BUSY', 'CANCEL'])
                    ->whereRaw('CHAR_LENGTH(ani) > 6')
            )
            ->selectable()
            ->selectCurrentPageOnly(false)
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('ani')
                    ->label('From')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('dnis')
                    ->label('To')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'NOANSWER' => 'warning',
                        'BUSY' => 'danger',
                        'CANCEL' => 'gray',
                        'CHANUNAVAIL' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('date')
                    ->label('Date')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date')
                    ->form([
                        DateTimePicker::make('date_from')
                            ->label('From')
                            ->withoutSeconds(),
                        DateTimePicker::make('date_to')
                            ->label('To')
                            ->withoutSeconds(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'] ?? null,
                                fn (Builder $q) => $q->where('date', '>=', $data['date_from'])
                            )
                            ->when(
                                $data['date_to'] ?? null,
                                fn (Builder $q) => $q->where('date', '<=', $data['date_to'])
                            );
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'CHANUNAVAIL' => 'Channel Unavailable',
                        'NOANSWER' => 'No Answer',
                        'BUSY' => 'Busy',
                        'CANCEL' => 'Cancel',
                    ]),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([10, 25, 50, 100])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (BulkAction $action): void {
                            $records = $action->getSelectedRecords();

                            // Get column map
                            $columnMap = collect(AbandonedCallExporter::getColumns())
                                ->mapWithKeys(fn (ExportColumn $column) => [
                                    $column->getName() => $column->getLabel(),
                                ])
                                ->all();

                            // Create export record
                            $export = Export::make([
                                'user_id' => auth()->id(),
                                'exporter' => AbandonedCallExporter::class,
                                'total_rows' => $records->count(),
                            ]);

                            $exporter = $export->getExporter(columnMap: $columnMap, options: []);
                            $export->file_disk = $exporter->getFileDisk();
                            $export->file_name = $exporter->getFileName($export);
                            $export->save();

                            // Dispatch export job
                            $columnMap = collect(AbandonedCallExporter::getColumns())
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
            ->striped();
    }
}
