<?php

use App\Http\Requests\StoreAnsweredCall;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/pbx-call-answered', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $number = $request['ani'];

    $agent = User::where('extension', $request['agent'])->first();

    Cache::forever('agent-in-call-'.$agent->id, 1);
    Cache::forever('call-'.$request['unique_id'], $agent->id);
    Cache::add('current-call-count', 0, 99999999);

    return response()->json(['status' => 'ok']);
});

Route::post('/pbx-call-disconnected', function (StoreAnsweredCall $request) {
    Log::info($request->all());

    $agent = User::where('extension', $request['agent'])->first();

    Cache::forget('agent-in-call-'.$agent->id);
    Cache::forget('call-'.$request['unique_id']);

    return response()->json(['status' => 'ok']);
});
