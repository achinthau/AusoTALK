<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatisticsService;
use Filament\Widgets\Widget;

class QueueWiseStatisticsWidget extends Widget
{
    protected string $view = 'filament.widgets.queue-wise-statistics-widget';

    protected int|string|array $columnSpan = 'full';

    public function getQueueData(): array
    {
        $statisticsService = app(DashboardStatisticsService::class);

        return $statisticsService->getQueueWiseStatistics();
    }
}
