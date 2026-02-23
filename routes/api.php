<?php

use App\Http\Requests\StoreAnsweredCall;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::post('/pbx-call-answered', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $tenant = $request['tenant'];
    $agent = User::where('extension', $request['dnis'])->first();

    $redis = Redis::connection()->client();
    $redis->select(1);
    $redis->set('agent_on_call-'.$tenant.'-'.$agent->id, $request['dnis']);
    $redis->set('call-'.$tenant.'-'.$request['dnis'], $agent->id);

    return response()->json(['status' => 'ok']);
});

Route::post('/pbx-call-disconnected', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $tenant = $request['tenant'];
    $agent = User::where('extension', $request['dnis'])->first();

    $redis = Redis::connection()->client();
    $redis->select(1);
    $redis->del('agent_on_call-'.$tenant.'-'.$agent->id);
    $redis->del('call-'.$tenant.'-'.$request['dnis']);

    return response()->json(['status' => 'ok']);
});
