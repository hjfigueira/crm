<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByDomainOrFallback
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return app(InitializeTenancyByDomain::class)->handle($request, $next);
        } catch (TenantCouldNotBeIdentifiedOnDomainException $e) {
            // Check if trying to access admin panel without tenant
            if ($request->is('admin') || $request->is('admin/*')) {
                // Only allow login and session transfer routes on central domain
                if (!$request->is('admin/login') && !$request->is('admin/auth/session')) {
                    abort(403, 'Admin panel is only accessible on tenant subdomains');
                }
            }

            // Allow access without tenant for central domain (login page, etc.)
            return $next($request);
        }
    }
}
