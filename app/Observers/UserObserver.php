<?php

namespace App\Observers;

use App\Models\Extension;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if ($user->extension) {
            Extension::where('number', $user->extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $oldExtension = $user->getOriginal('extension');
        $newExtension = $user->extension;

        // If extension was unassigned or changed
        if ($oldExtension && $oldExtension !== $newExtension) {
            Extension::where('number', $oldExtension)
                ->update(['status' => 0]);
        }

        // If new extension is being assigned
        if ($newExtension && $newExtension !== $oldExtension) {
            Extension::where('number', $newExtension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if ($user->extension) {
            Extension::where('number', $user->extension)
                ->update(['status' => 0]);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        if ($user->extension) {
            Extension::where('number', $user->extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        if ($user->extension) {
            Extension::where('number', $user->extension)
                ->update(['status' => 0]);
        }
    }
}
