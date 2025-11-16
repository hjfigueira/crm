<?php

namespace App\Models\Concerns;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    /**
     * Boot the trait and attach global scope & model events.
     */
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model): void {
            // If not set explicitly, resolve from current session / app context
            if ($model->getAttribute('tenant_id') === null) {
                $tenantId = self::resolveCurrentTenantId();
                if ($tenantId !== null) {
                    $model->setAttribute('tenant_id', $tenantId);
                }
            }
        });

        static::updating(function (Model $model): void {
            // Prevent changing tenant_id after creation
            if ($model->isDirty('tenant_id')) {
                $model->setAttribute('tenant_id', $model->getOriginal('tenant_id'));
            }
        });
    }

    /**
     * Resolve the current tenant id from app context / session / auth.
     */
    protected static function resolveCurrentTenantId(): ?int
    {
        // Prefer app-bound currentTenant set by middleware
        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');
            if ($tenant && isset($tenant->id)) {
                return (int) $tenant->id;
            }
        }

        // Fallback to session
        $sessionTenantId = session('tenant_id');
        if ($sessionTenantId !== null) {
            return (int) $sessionTenantId;
        }

        // Fallback to user's first tenant, if available
        if (auth()->check() && method_exists(auth()->user(), 'tenants')) {
            $id = auth()->user()->tenants()->value('tenants.id');
            return $id !== null ? (int) $id : null;
        }

        return null;
    }
}
