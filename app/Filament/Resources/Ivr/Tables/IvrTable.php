<?php

namespace App\Filament\Resources\Ivr\Tables;

use App\Filament\Exports\IvrExporter;
use App\Models\AuIvrCall;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IvrTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->selectable()
            ->selectCurrentPageOnly(false)
            ->columns([
                TextColumn::make('date')
                    ->label('Call Date & Time')
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

                TextColumn::make('ivr')
                    ->label('IVR')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('bill_sec_duration')
                    ->label('Duration')
                    ->getStateUsing(function (AuIvrCall $record): string {
                        return $record->bill_sec_duration;
                    }),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with('cdr'))
            ->filters([
                Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from_date')
                            ->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('to_date')
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

                SelectFilter::make('ivr')
                    ->options(function (): array {
                        return AuIvrCall::distinct('ivr')
                            ->pluck('ivr', 'ivr')
                            ->toArray();
                    }),
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
                                ->mapWithKeys(fn (\Filament\Actions\Exports\ExportColumn $column) => [
                                    $column->getName() => $column->getLabel(),
                                ])
                                ->all();

                            // Create export record
                            $export = \Filament\Actions\Exports\Models\Export::make([
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
            ->paginated([10, 25, 50, 100]);
    }
}
