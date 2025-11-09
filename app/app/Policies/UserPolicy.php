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
        // Allow if user has permission in current tenant or is Super Admin (handled by Gate::before)
        if (method_exists($user, 'can')) {
            return $user->can('view users');
        }

        return false;
    }

    /**
     * Determine whether the user can create users within the current tenant.
     */
    public function create(User $user): bool
    {
        // Super Admin shortcut handled in Gate::before
        if (method_exists($user, 'can')) {
            // Tenant Admins should have `manage users` permission within the current tenant context
            return $user->can('manage users');
        }

        return false;
    }

    /**
     * Determine whether the user can update the given user.
     */
    public function update(User $user, User $model): bool
    {
        if (method_exists($user, 'can')) {
            return $user->can('manage users');
        }

        return false;
    }

    /**
     * Determine whether the user can delete the given user.
     */
    public function delete(User $user, User $model): bool
    {
        if (method_exists($user, 'can')) {
            return $user->can('manage users');
        }

        return false;
    }
}
