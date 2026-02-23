<?php

namespace App\Filament\Widgets;

use App\Services\DashboardStatisticsService;
use Filament\Widgets\Widget;

class CallStatisticsWidget extends Widget
{
    protected string $view = 'filament.widgets.call-statistics-widget';

    protected int|string|array $columnSpan = 'full';

    public int $totalCalls = 0;

    public int $inboundCalls = 0;

    public int $outboundCalls = 0;

    public int $internalCalls = 0;

    public int $answeredCalls = 0;

    public int $abandonedCalls = 0;

    public int $waitingCalls = 0;

    public int $ongoingCalls = 0;

    public function mount(): void
    {
        $service = app(DashboardStatisticsService::class);

        $callStats = $service->getCallStatistics();
        $this->totalCalls = $callStats['total'];
        $this->inboundCalls = $callStats['inbound'];
        $this->outboundCalls = $callStats['outbound'];

        $queueStats = $service->getQueueStatistics();
        $this->internalCalls = $callStats['internal'];
        $this->answeredCalls = $queueStats['answered'];
        $this->abandonedCalls = $queueStats['abandoned'];
        $this->waitingCalls = $queueStats['waiting'];

        $this->ongoingCalls = $service->getOngoingCallCount();
    }
}
