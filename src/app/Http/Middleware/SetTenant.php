<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
//        if ($request->user()) {
//            $tenantId = session('tenant_id');
//
//            if ($tenantId === null) {
//                $tenantId = $request->user()->tenants()->value('tenants.id');
//                if ($tenantId !== null) {
//                    session(['tenant_id' => $tenantId]);
//                }
//            }
//
//            if ($tenantId !== null) {
//                $tenant = Tenant::query()->find($tenantId);
//                if ($tenant) {
//                    app()->instance('currentTenant', $tenant);
//                }
//            }
//
//            // Configure Spatie Permission team context to the current tenant
//            if (class_exists(PermissionRegistrar::class)) {
//                /** @var PermissionRegistrar $registrar */
//                $registrar = app(PermissionRegistrar::class);
//                $registrar->setPermissionsTeamId($tenantId);
//            }
//        }

        return $next($request);
    }
}
