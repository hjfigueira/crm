<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create users within the current tenant.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the given user.
     */
    public function update(User $user, User $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the given user.
     */
    public function delete(User $user, User $model): bool
    {
        return true;
    }
}
