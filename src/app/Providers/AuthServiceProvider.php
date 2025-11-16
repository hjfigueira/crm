<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => \App\Policies\UserPolicy::class,
    ];

    public function boot(): void
    {
        // Super Admin override: allow any ability for users with the "Super Admin" role.
        // Team-agnostic: pass null team (global assignment) OR rely on current team context.
        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasRole')) {
                if ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin')) {
                    return true;
                }
            }

            return null; // continue to normal permission checks
        });
    }
}
