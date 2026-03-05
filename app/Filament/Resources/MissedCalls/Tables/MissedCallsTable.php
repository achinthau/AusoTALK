<?php

namespace App\Filament\Resources\MissedCalls\Tables;

use App\Filament\Exports\AbandonedCallExporter;
use App\Models\AbandonedNew;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MissedCallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(AbandonedNew::query())
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

                // TextColumn::make('queuename')
                //     ->label('Skill')
                //     ->sortable()
                //     ->searchable(),

                TextColumn::make('recalled_status')
                    ->label('Recalled Status')
                    ->sortable()
                    ->formatStateUsing(function ($state): string {
                        return $state == 1 ? '✓' : '✗';
                    })
                    ->html(),

                TextColumn::make('received_time')
                    ->label('Received')
                    ->sortable(),

                TextColumn::make('recalled_time')
                    ->label('Recalled')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('received_time')
                    ->form([
                        DateTimePicker::make('received_from')
                            ->label('From')
                            ->withoutSeconds(),
                        DateTimePicker::make('received_to')
                            ->label('To')
                            ->withoutSeconds(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['received_from'] ?? null,
                                fn (Builder $q) => $q->where('received_time', '>=', $data['received_from'])
                            )
                            ->when(
                                $data['received_to'] ?? null,
                                fn (Builder $q) => $q->where('received_time', '<=', $data['received_to'])
                            );
                    }),

                SelectFilter::make('recalled_status')
                    ->label('Recalled Status')
                    ->options([
                        1 => 'Recalled',
                        0 => 'Not Recalled',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] !== null && $data['value'] !== '',
                            fn (Builder $q) => $q->where('recalled_status', (int) $data['value'])
                        );
                    }),

                // SelectFilter::make('queuename')
                //     ->label('Skill')
                //     ->options(
                //         AbandonedNew::query()
                //             ->distinct('queuename')
                //             ->whereNotNull('queuename')
                //             ->pluck('queuename', 'queuename')
                //             ->filter(fn ($value) => ! empty($value))
                //             ->toArray()
                //     )
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query->when(
                //             $data['value'] ?? null,
                //             fn (Builder $q) => $q->where('queuename', $data['value'])
                //         );
                //     }),
            ])
            ->defaultSort('received_time', 'desc')
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
                                ->mapWithKeys(fn (\Filament\Actions\Exports\ExportColumn $column) => [
                                    $column->getName() => $column->getLabel(),
                                ])
                                ->all();

                            // Create export record
                            $export = \Filament\Actions\Exports\Models\Export::make([
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
                                ->mapWithKeys(fn (\Filament\Actions\Exports\ExportColumn $column) => [
                                    $column->getName() => $column->getLabel(),
                                ])
                                ->all();

                            // Get the query for selected records only
                            $query = $records->toQuery();
                            $serializedQuery = \AnourValar\EloquentSerialize\Facades\EloquentSerializeFacade::serialize($query);

                            $formats = [ExportFormat::Csv, ExportFormat::Xlsx];
                            $hasXlsx = in_array(ExportFormat::Xlsx, $formats);
                            $hasCsv = in_array(ExportFormat::Csv, $formats);

                            $makeCreateXlsxFileJob = fn () => new \Filament\Actions\Exports\Jobs\CreateXlsxFile(
                                export: $export,
                                columnMap: $columnMap,
                            );

                            $jobs = [
                                \Illuminate\Support\Facades\Bus::batch([
                                    app(\Filament\Actions\Exports\Jobs\PrepareCsvExport::class, [
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
                            $jobs[] = app(\Filament\Actions\Exports\Jobs\ExportCompletion::class, [
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

                            \Illuminate\Support\Facades\Bus::chain($jobs)->dispatch();

                            \Filament\Notifications\Notification::make()
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
