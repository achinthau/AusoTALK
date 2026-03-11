<?php

use App\Http\Controllers\DashboardController;
use App\Http\Requests\StoreAnsweredCall;
use App\Models\User;
use App\Services\DashboardStatisticsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

function broadcastAgentStatus(int $userId, int $companyId, bool $isOnCall, string $type = 'primary'): void
{
    try {
        Http::timeout(2)->post('http://localhost:'.config('services.ws.port').'/broadcast', [
            'secret' => config('services.ws.secret'),
            'event' => 'agent-status-updated',
            'data' => [
                'userId' => $userId,
                'companyId' => $companyId,
                'isOnCall' => $isOnCall,
                'type' => $type,
            ],
        ]);
    } catch (\Exception $e) {
        Log::error('Error broadcasting agent status: '.$e->getMessage());
    }
}

Route::match(['GET', 'POST'], '/pbx-call-answered', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $tenant = $request['tenant'];
    $type = $request['type'];
    $extensionField = $type === 'primary' ? 'primary_extension' : 'secondary_extension';
    $agent = User::where($extensionField, $request['dnis'])->first();

    if (! $agent) {
        Log::warning('pbx-call-answered: no user with extension '.$request['dnis']);

        return response()->json(['status' => 'ok', 'warning' => 'agent not found']);
    }

    $redis = Redis::connection()->client();
    $redis->select(1);
    $redis->set('agent_on_call-'.$tenant.'-'.$agent->id, json_encode([
        'extension' => $request['dnis'],
        'type' => $type,
    ]));
    $redis->set('call-'.$tenant.'-'.$request['dnis'], $agent->id);

    // Broadcast agent status update to WebSocket
    broadcastAgentStatus($agent->id, $agent->company_id, true, $type);

    return response()->json(['status' => 'ok']);
});

Route::match(['GET', 'POST'], '/pbx-call-disconnected', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $tenant = $request['tenant'];
    $type = $request['type'];
    $extensionField = $type === 'primary' ? 'primary_extension' : 'secondary_extension';
    $agent = User::where($extensionField, $request['dnis'])->first();

    if (! $agent) {
        Log::warning('pbx-call-disconnected: no user with extension '.$request['dnis']);

        return response()->json(['status' => 'ok', 'warning' => 'agent not found']);
    }

    $redis = Redis::connection()->client();
    $redis->select(1);
    $redis->del('agent_on_call-'.$tenant.'-'.$agent->id);
    $redis->del('call-'.$tenant.'-'.$request['dnis']);

    // Broadcast agent status update to WebSocket
    broadcastAgentStatus($agent->id, $agent->company_id, false, $type);

    return response()->json(['status' => 'ok']);
});

// Get agent on-call status (public endpoint for polling)
Route::get('/agents/{agentId}/status', function ($agentId) {
    $agent = User::find($agentId);
    if (! $agent) {
        return response()->json(['error' => 'Agent not found'], 404);
    }

    $isOnCall = false;
    $callType = null;
    if ($agent->company) {
        $redis = Redis::connection()->client();
        $redis->select(1);
        $callData = $redis->get("agent_on_call-{$agent->company->context}-{$agent->id}");
        if ($callData) {
            $isOnCall = true;
            $decoded = json_decode($callData, true);
            $callType = $decoded['type'] ?? 'primary';
        }
    }

    return response()->json([
        'agentId' => $agent->id,
        'isOnCall' => $isOnCall,
        'callType' => $callType,
    ]);
});

// Batch agent status endpoint - single request for all agents on the page
Route::post('/agents/status-batch', function (\Illuminate\Http\Request $request) {
    $agentIds = $request->input('ids', []);
    if (! is_array($agentIds) || count($agentIds) === 0) {
        return response()->json(['agents' => []]);
    }

    // Limit to 200 agents max to prevent abuse
    $agentIds = array_slice(array_map('intval', $agentIds), 0, 200);

    $agents = User::with('company')->whereIn('id', $agentIds)->get()->keyBy('id');

    $redis = Redis::connection()->client();
    $redis->select(1);

    $results = [];
    foreach ($agentIds as $id) {
        $agent = $agents->get($id);
        if (! $agent || ! $agent->company) {
            $results[$id] = ['isOnCall' => false, 'callType' => null];

            continue;
        }

        $callData = $redis->get("agent_on_call-{$agent->company->context}-{$id}");
        if ($callData) {
            $decoded = json_decode($callData, true);
            $results[$id] = ['isOnCall' => true, 'callType' => $decoded['type'] ?? 'primary'];
        } else {
            $results[$id] = ['isOnCall' => false, 'callType' => null];
        }
    }

    return response()->json(['agents' => $results]);
});

// Get call statistics (public endpoint for polling)
Route::get('/call-statistics', function () {
    $service = app(DashboardStatisticsService::class);

    return response()->json([
        'calls' => $service->getCallStatistics(),
        'queue' => $service->getQueueStatistics(),
        'ongoing' => $service->getOngoingCallCount(),
    ]);
});

// Authenticated routes for dashboard statistics (fallback when WebSocket is unavailable)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics']);
});
