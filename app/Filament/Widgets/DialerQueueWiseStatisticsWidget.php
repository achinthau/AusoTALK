<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatisticsService;
use Filament\Widgets\Widget;

class DialerQueueWiseStatisticsWidget extends Widget
{
    protected string $view = 'filament.widgets.dialer-queue-wise-statistics-widget';

    protected int|string|array $columnSpan = 'full';

    public bool $isExpanded = true;

    public function getDialerQueueData(): array
    {
        $statisticsService = app(DashboardStatisticsService::class);

        return $statisticsService->getDialerQueueWiseStatistics();
    }

    public function toggleExpand(): void
    {
        $this->isExpanded = ! $this->isExpanded;
    }
}
