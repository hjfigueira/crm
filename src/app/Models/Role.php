<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

/**
 * Application Role model extending Spatie's Role to add tenant ownership.
 */
class Role extends SpatieRole
{
    use TenantConnection;
}
