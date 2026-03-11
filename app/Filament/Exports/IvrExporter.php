<?php

namespace App\Filament\Exports;

use App\Models\Callcount;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class IvrExporter extends Exporter
{
    protected static ?string $model = Callcount::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date')
                ->label('Date'),
            ExportColumn::make('ani')
                ->label('Source'),
            ExportColumn::make('dnis')
                ->label('Destination'),
            ExportColumn::make('direction')
                ->label('Direction')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'in' => 'Inbound',
                    'out' => 'Outbound',
                    default => $state,
                }),
        ];
    }

    public function getFileName(Export $export): string
    {
        return "ivr-report-{$export->getKey()}";
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return "Your IVR export with {$export->successful_rows} row(s) has been completed.";
    }
}
