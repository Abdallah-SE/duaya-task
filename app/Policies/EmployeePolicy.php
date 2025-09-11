<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('employee');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Employee $employee): bool
    {
        // Admin can view all employees
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Employee can view themselves and other employees
        if ($user->hasRole('employee')) {
            return $user->id === $employee->user_id || $employee->user->hasRole('employee');
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('employee');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Employee $employee): bool
    {
        // Admin can update all employees
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Employee can update themselves and other employees
        if ($user->hasRole('employee')) {
            return $user->id === $employee->user_id || $employee->user->hasRole('employee');
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Employee $employee): bool
    {
        // Admin can delete all employees except themselves
        if ($user->hasRole('admin')) {
            return $user->id !== $employee->user_id;
        }
        
        // Employee can delete other employees but not themselves
        if ($user->hasRole('employee')) {
            return $user->id !== $employee->user_id && $employee->user->hasRole('employee');
        }
        
        return false;
    }
}
