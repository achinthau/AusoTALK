<?php

namespace App\Filament\Resources\Ivr\Pages;

use App\Filament\Resources\Ivr\IvrResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListIvr extends ListRecords
{
    protected static string $resource = IvrResource::class;

    protected ?string $heading = 'IVR Report';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('analytics')
                ->label('View Analytics')
                ->icon('heroicon-o-chart-bar')
                ->url(AnalyticsIvr::getUrl()),
            // ->openUrlInNewTab(),
        ];
    }
}
