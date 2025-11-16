<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Application Role model extending Spatie's Role to add tenant ownership.
 */
class Role extends SpatieRole
{
    /**
     * Get the tenant that owns the role.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

}
