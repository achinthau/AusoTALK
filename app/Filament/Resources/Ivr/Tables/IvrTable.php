<?php

namespace App\Filament\Resources\Ivr\Tables;

use AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade;
use App\Filament\Exports\IvrExporter;
use App\Models\Callcount;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;

class IvrTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->selectable()
            ->selectCurrentPageOnly(false)
            ->query(
                Callcount::query()
                    ->where('status', 1)
                    ->where(function (Builder $query) {
                        $query->where(function (Builder $q) {
                            $q->where('direction', 'in')
                                ->whereRaw('CHAR_LENGTH(ani) > 6');
                        })->orWhere(function (Builder $q) {
                            $q->where('direction', 'out')
                                ->whereRaw('CHAR_LENGTH(dnis) > 6');
                        });
                    })
            )
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('ani')
                    ->label('Source')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('dnis')
                    ->label('Destination')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('direction')
                    ->label('Direction')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in' => 'Inbound',
                        'out' => 'Outbound',
                        default => $state,
                    }),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from_date')
                            ->label('From Date'),
                        DatePicker::make('to_date')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $q) => $q->whereDate('date', '>=', $data['from_date'])
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $q) => $q->whereDate('date', '<=', $data['to_date'])
                            );
                    }),

                SelectFilter::make('direction')
                    ->options([
                        'in' => 'Inbound',
                        'out' => 'Outbound',
                    ]),
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
                            $columnMap = collect(IvrExporter::getColumns())
                                ->mapWithKeys(fn (ExportColumn $column) => [
                                    $column->getName() => $column->getLabel(),
                                ])
                                ->all();

                            // Create export record
                            $export = Export::make([
                                'user_id' => auth()->id(),
                                'exporter' => IvrExporter::class,
                                'total_rows' => $records->count(),
                            ]);

                            $exporter = $export->getExporter(columnMap: $columnMap, options: []);
                            $export->file_disk = $exporter->getFileDisk();
                            $export->file_name = $exporter->getFileName($export);
                            $export->save();

                            // Dispatch export job using Filament's internal pattern
                            $columnMap = collect(IvrExporter::getColumns())
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
