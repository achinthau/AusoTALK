<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('user');
    }

    public function view(User $user, Department $department): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('user')) {
            return $user->company_id === $department->company_id;
        }
        
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('user');
    }

    public function update(User $user, Department $department): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('user')) {
            return $user->company_id === $department->company_id;
        }
        
        return false;
    }

    public function delete(User $user, Department $department): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        if ($user->hasRole('user')) {
            return $user->company_id === $department->company_id;
        }
        
        return false;
    }

    public function restore(User $user, Department $department): bool
    {
        return $this->delete($user, $department);
    }

    public function forceDelete(User $user, Department $department): bool
    {
        return $this->delete($user, $department);
    }
}
