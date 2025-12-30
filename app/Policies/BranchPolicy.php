<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('user');
    }

    public function view(User $user, Branch $branch): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('user')) {
            return $user->company_id === $branch->company_id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('user');
    }

    public function update(User $user, Branch $branch): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('user')) {
            return $user->company_id === $branch->company_id;
        }
        
        return false;
    }

    public function delete(User $user, Branch $branch): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('user')) {
            return $user->company_id === $branch->company_id;
        }
        
        return false;
    }

    public function restore(User $user, Branch $branch): bool
    {
        return $this->delete($user, $branch);
    }

    public function forceDelete(User $user, Branch $branch): bool
    {
        return $this->delete($user, $branch);
    }
}
