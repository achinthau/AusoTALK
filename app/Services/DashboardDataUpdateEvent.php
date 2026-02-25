<?php

namespace App\Services;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class DashboardDataUpdateEvent implements ShouldBroadcast
{
    public function __construct(
        public array $statistics,
        public string $tenant,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("dashboard-updates-{$this->tenant}");
    }

    public function broadcastAs(): string
    {
        return 'statistics-updated';
    }
}
