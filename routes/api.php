<?php

use App\Http\Controllers\DashboardController;
use App\Http\Requests\StoreAnsweredCall;
use App\Models\User;
use App\Services\DashboardStatisticsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

function notifyDashboard(): void
{
    $stats = app(DashboardStatisticsService::class);

    Http::timeout(2)->post('http://localhost:'.config('services.ws.port').'/broadcast', [
        'secret' => config('services.ws.secret'),
        'event' => 'statistics-updated',
        'data' => [
            'calls' => $stats->getCallStatistics(),
            'queue' => $stats->getQueueStatistics(),
            'ongoing' => $stats->getOngoingCallCount(),
        ],
    ]);
}

Route::match(['GET', 'POST'], '/pbx-call-answered', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $tenant = $request['tenant'];
    $agent = User::where('extension', $request['dnis'])->first();

    if (! $agent) {
        Log::warning('pbx-call-answered: no user with extension '.$request['dnis']);

        return response()->json(['status' => 'ok', 'warning' => 'agent not found']);
    }

    $redis = Redis::connection()->client();
    $redis->select(1);
    $redis->set('agent_on_call-'.$tenant.'-'.$agent->id, $request['dnis']);
    $redis->set('call-'.$tenant.'-'.$request['dnis'], $agent->id);


    return response()->json(['status' => 'ok']);
});

Route::match(['GET', 'POST'], '/pbx-call-disconnected', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $tenant = $request['tenant'];
    $agent = User::where('extension', $request['dnis'])->first();

    if (! $agent) {
        Log::warning('pbx-call-disconnected: no user with extension '.$request['dnis']);

        return response()->json(['status' => 'ok', 'warning' => 'agent not found']);
    }

    $redis = Redis::connection()->client();
    $redis->select(1);
    $redis->del('agent_on_call-'.$tenant.'-'.$agent->id);
    $redis->del('call-'.$tenant.'-'.$request['dnis']);


    return response()->json(['status' => 'ok']);
});

// Authenticated routes for dashboard statistics (fallback when WebSocket is unavailable)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
});
