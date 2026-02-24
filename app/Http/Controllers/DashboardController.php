<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatisticsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function getStatistics(DashboardStatisticsService $statisticsService): JsonResponse
    {
        $user = auth()->user();

        if (! $user || ! $user->company) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $statistics = [
            'calls' => $statisticsService->getCachedCallStatistics($user->company->context),
            'queue' => $statisticsService->getCachedQueueStatistics($user->company->context),
            'ongoing' => $statisticsService->getCachedOngoingCallCount($user->company->context),
            'queueWise' => $statisticsService->getCachedQueueWiseStatistics($user->company->context),
            'dialerQueueWise' => $statisticsService->getCachedDialerQueueWiseStatistics($user->company->context),
        ];

        return response()->json($statistics);
    }
}
