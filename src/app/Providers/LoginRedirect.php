<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class LoginRedirect implements LoginResponse
{
    public function toResponse($request): Response|RedirectResponse
    {
        $tenant = $request->user()->tenant;
        return new RedirectResponse("http://tenant.localhost:8080/admin");
    }
}
