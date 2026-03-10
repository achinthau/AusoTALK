<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatisticsService;
use Filament\Widgets\Widget;

class CallStatisticsWidget extends Widget
{
    protected string $view = 'filament.widgets.call-statistics-widget';

    protected int|string|array $columnSpan = 'full';

    public bool $isExpanded = true;

    public int $inboundCalls = 0;

    public int $outboundCalls = 0;

    public int $internalCalls = 0;

    public int $answeredCalls = 0;

    public int $ongoingCalls = 0;

    public int $abandonedCalls = 0;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refreshStats(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $service = app(DashboardStatisticsService::class);

        $callStats = $service->getCallStatistics();
        $this->inboundCalls = $callStats['inbound'];
        $this->outboundCalls = $callStats['outbound'];
        $this->internalCalls = $callStats['internal'];

        $queueStats = $service->getQueueStatistics();
        $this->answeredCalls = $queueStats['answered'];
        $this->abandonedCalls = $queueStats['abandoned'];

        $this->ongoingCalls = $service->getOngoingCallCount();
    }

    public function toggleExpand(): void
    {
        $this->isExpanded = ! $this->isExpanded;
    }
}
