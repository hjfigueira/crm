<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Application Permission model extending Spatie's Permission to add tenant ownership.
 */
class Permission extends SpatiePermission
{
    /**
     * Get the tenant that owns the permission.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
