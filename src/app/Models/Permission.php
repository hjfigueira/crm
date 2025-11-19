<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

/**
 * Application Permission model extending Spatie's Permission to add tenant ownership.
 */
class Permission extends SpatiePermission
{
    use TenantConnection;
}
