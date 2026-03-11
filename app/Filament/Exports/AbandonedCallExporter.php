<?php

namespace App\Filament\Exports;

use App\Models\PbxCallaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AbandonedCallExporter extends Exporter
{
    protected static ?string $model = PbxCallaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('Id'),
            ExportColumn::make('ani')
                ->label('From'),
            ExportColumn::make('dnis')
                ->label('To'),
            ExportColumn::make('status')
                ->label('Status')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'CHANUNAVAIL' => 'Unavailable',
                    'NOANSWER' => 'No Answer',
                    'BUSY' => 'Busy',
                    'CANCEL' => 'Cancel',
                    default => $state,
                }),
            ExportColumn::make('date')
                ->label('Date'),
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
