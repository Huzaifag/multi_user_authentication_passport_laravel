<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    /**
     * Determine if the user can manage the admin role.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function manageAdmin(User $user)
    {
        return $user->role === 'admin';
    }

    /**
     * Determine if the user can manage the manager role.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function manageManager(User $user)
    {
        return $user->role === 'manager';
    }

    /**
     * Determine if the user can manage the employee role.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function manageEmployee(User $user)
    {
        return $user->role === 'employee';
    }
}

