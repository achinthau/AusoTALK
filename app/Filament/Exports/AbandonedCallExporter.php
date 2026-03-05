<?php

namespace App\Filament\Exports;

use App\Models\AbandonedNew;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AbandonedCallExporter extends Exporter
{
    protected static ?string $model = AbandonedNew::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Id'),
            ExportColumn::make('ani')
                ->label('From'),
            ExportColumn::make('dnis')
                ->label('To'),
            ExportColumn::make('queuename')
                ->label('Skill'),
            ExportColumn::make('recalled_status')
                ->label('Recalled Status')
                ->formatStateUsing(fn ($state) => $state == 1 ? 'Recalled' : 'Not Recalled'),
            ExportColumn::make('received_time')
                ->label('Received'),
            ExportColumn::make('recalled_time')
                ->label('Recalled'),
        ];
    }

    public function getFileName(Export $export): string
    {
        return "missed-calls-report-{$export->getKey()}";
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return "Your Missed Calls export with {$export->successful_rows} row(s) has been completed.";
    }
}
