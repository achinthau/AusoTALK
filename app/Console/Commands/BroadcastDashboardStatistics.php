<?php

namespace App\Console\Commands;

use App\Services\DashboardDataUpdateEvent;
use App\Services\DashboardStatisticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class BroadcastDashboardStatistics extends Command
{
    protected $signature = 'dashboard:broadcast-statistics {--interval=3 : Interval in seconds between broadcasts}';

    protected $description = 'Broadcast dashboard statistics via WebSocket';

    public function handle(DashboardStatisticsService $statisticsService): int
    {
        $interval = (int) $this->option('interval');

        $this->info("Broadcasting dashboard statistics every {$interval} second(s)...");

        while (true) {
            try {
                $statistics = [
                    'calls' => $statisticsService->getCallStatistics(),
                    'queue' => $statisticsService->getQueueStatistics(),
                    'ongoing' => $statisticsService->getOngoingCallCount(),
                    'queueWise' => $statisticsService->getQueueWiseStatistics(),
                    'dialerQueueWise' => $statisticsService->getDialerQueueWiseStatistics(),
                ];

                Event::dispatch(new DashboardDataUpdateEvent($statistics));

                sleep($interval);
            } catch (\Exception $e) {
                $this->error("Error broadcasting statistics: {$e->getMessage()}");
                sleep($interval);
            }
        }

        return 0;
    }
}
