<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private channels for company-scoped dashboard updates
Broadcast::private('dashboard-updates-{tenant}', function ($user, $tenant) {
    return $user && $user->company && $user->company->context === $tenant;
});
