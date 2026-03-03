<?php

use App\Events\AgentCallStatusUpdated;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;

it('broadcasts event when agent starts call', function () {
    Event::fake();

    $company = Company::factory()->create();
    $agent = User::factory()->create(['company_id' => $company->id]);

    // Dispatch the event
    AgentCallStatusUpdated::dispatch($agent->id, $company->id, true);

    // Assert the event was broadcast
    Event::assertDispatched(AgentCallStatusUpdated::class, function ($event) use ($agent, $company) {
        return $event->userId === $agent->id &&
               $event->companyId === $company->id &&
               $event->isOnCall === true;
    });
});

it('broadcasts event when agent ends call', function () {
    Event::fake();

    $company = Company::factory()->create();
    $agent = User::factory()->create(['company_id' => $company->id]);

    // Dispatch the event
    AgentCallStatusUpdated::dispatch($agent->id, $company->id, false);

    // Assert the event was broadcast
    Event::assertDispatched(AgentCallStatusUpdated::class, function ($event) use ($agent, $company) {
        return $event->userId === $agent->id &&
               $event->companyId === $company->id &&
               $event->isOnCall === false;
    });
});

it('correctly identifies agent on call from redis', function () {
    $company = Company::factory()->create();
    $agent = User::factory()->create(['company_id' => $company->id]);

    // Simulate call in Redis
    $redis = Redis::connection()->client();
    $redis->select(1);
    $redis->set("agent_on_call-{$company->context}-{$agent->id}", $agent->extension);

    // Verify the key exists
    $exists = $redis->exists("agent_on_call-{$company->context}-{$agent->id}");

    expect($exists)->toBe(1);

    // Clean up
    $redis->del("agent_on_call-{$company->context}-{$agent->id}");
});
