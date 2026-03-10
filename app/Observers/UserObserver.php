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

        if ($user->primary_extension) {
            Extension::where('number', $user->primary_extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }

        if ($user->secondary_extension) {
            Extension::where('number', $user->secondary_extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Handle old 'extension' column
        $oldExtension = $user->getOriginal('extension');
        $newExtension = $user->extension;

        if ($oldExtension && $oldExtension !== $newExtension) {
            Extension::where('number', $oldExtension)
                ->update(['status' => 0]);
        }

        if ($newExtension && $newExtension !== $oldExtension) {
            Extension::where('number', $newExtension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }

        // Handle 'primary_extension' column
        $oldPrimaryExtension = $user->getOriginal('primary_extension');
        $newPrimaryExtension = $user->primary_extension;

        if ($oldPrimaryExtension && $oldPrimaryExtension !== $newPrimaryExtension) {
            Extension::where('number', $oldPrimaryExtension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 0]);
        }

        if ($newPrimaryExtension && $newPrimaryExtension !== $oldPrimaryExtension) {
            Extension::where('number', $newPrimaryExtension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }

        // Handle 'secondary_extension' column
        $oldSecondaryExtension = $user->getOriginal('secondary_extension');
        $newSecondaryExtension = $user->secondary_extension;

        if ($oldSecondaryExtension && $oldSecondaryExtension !== $newSecondaryExtension) {
            Extension::where('number', $oldSecondaryExtension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 0]);
        }

        if ($newSecondaryExtension && $newSecondaryExtension !== $oldSecondaryExtension) {
            Extension::where('number', $newSecondaryExtension)
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

        if ($user->primary_extension) {
            Extension::where('number', $user->primary_extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 0]);
        }

        if ($user->secondary_extension) {
            Extension::where('number', $user->secondary_extension)
                ->where('company_id', $user->company_id)
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

        if ($user->primary_extension) {
            Extension::where('number', $user->primary_extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 1]);
        }

        if ($user->secondary_extension) {
            Extension::where('number', $user->secondary_extension)
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

        if ($user->primary_extension) {
            Extension::where('number', $user->primary_extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 0]);
        }

        if ($user->secondary_extension) {
            Extension::where('number', $user->secondary_extension)
                ->where('company_id', $user->company_id)
                ->update(['status' => 0]);
        }
    }
}
