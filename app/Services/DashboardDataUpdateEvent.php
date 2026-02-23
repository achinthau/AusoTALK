<?php

namespace App\Services;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DashboardDataUpdateEvent implements ShouldBroadcast
{
    public function __construct(public array $statistics) {}

    public function broadcastOn(): Channel
    {
        return new Channel('dashboard-updates');
    }

    public function broadcastAs(): string
    {
        return 'statistics-updated';
    }
}
