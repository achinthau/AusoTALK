<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\DashboardStatisticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BroadcastDashboardStatistics extends Command
{
    protected $signature = 'dashboard:broadcast-statistics {--interval=2 : Interval in seconds between broadcasts}';

    protected $description = 'Broadcast dashboard statistics via WebSocket to each company';

    public function handle(DashboardStatisticsService $statisticsService): int
    {
        $interval = (int) $this->option('interval');

        $this->info("Broadcasting dashboard statistics every {$interval} second(s) to all companies...");

        while (true) {
            try {
                // Get all active companies
                $companies = Company::select('id', 'context')->get();

                foreach ($companies as $company) {
                    if (! $company->context) {
                        continue;
                    }

                    // Get cached statistics for this company
                    $statistics = [
                        'calls' => $statisticsService->getCachedCallStatistics($company->context),
                        'queue' => $statisticsService->getCachedQueueStatistics($company->context),
                        'ongoing' => $statisticsService->getCachedOngoingCallCount($company->context),
                    ];
                    $this->line("Broadcasting to {$company->context} - Ongoing: {$statistics['ongoing']}");

                    // Broadcast to WebSocket via HTTP
                    Http::timeout(2)->post('http://localhost:'.config('services.ws.port').'/broadcast', [
                        'secret' => config('services.ws.secret'),
                        'event' => 'statistics-updated',
                        'data' => $statistics,
                    ]);
                }

                sleep($interval);
            } catch (\Exception $e) {
                $this->error("Error broadcasting statistics: {$e->getMessage()}");
                sleep($interval);
            }
        }

        return 0;
    }
}

// php artisan dashboard:broadcast-statistics --interval=2
