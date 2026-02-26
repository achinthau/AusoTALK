<?php

namespace App\Filament\Exports;

use App\Models\Cdr;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CdrsExporter extends Exporter
{
    protected static ?string $model = Cdr::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('calldate')
                ->label('Call Date & Time'),
            ExportColumn::make('src')
                ->label('Source'),
            ExportColumn::make('dst')
                ->label('Destination'),
            ExportColumn::make('extension')
                ->label('Extension'),
            ExportColumn::make('direction')
                ->label('Direction'),
            ExportColumn::make('bill_sec')
                ->label('Duration (Seconds)'),
            ExportColumn::make('billsec')
                ->label('Billable Seconds'),
            ExportColumn::make('disposition')
                ->label('Disposition'),
            ExportColumn::make('dcontext')
                ->label('Context'),
        ];
    }

    public function getFileName(Export $export): string
    {
        return "cdr-report-{$export->getKey()}";
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return "Your CDR export with {$export->successful_rows} row(s) has been completed.";
    }
}
