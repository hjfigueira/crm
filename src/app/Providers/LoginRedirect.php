<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LoginRedirect implements LoginResponse
{
    public function toResponse($request)
    {
        $user = $request->user();

        // Get the user's first tenant
        $tenant = $user->tenant()->first();

        if ($tenant && $tenant->domains()->exists()) {

            // Get the first domain for this tenant
            $domain = $tenant->domains()->first();

            // Build the redirect URL
            $url = $request->getScheme() . '://' . $domain->domain;

            if ($request->getPort() && !in_array($request->getPort(), [80, 443])) {
                $url .= ':' . $request->getPort();
            }

            $url .= '/admin';

            // Use redirect() helper which returns proper response for Livewire
            return redirect()->to($url);
        }

        // Fallback to default Filament redirect
        return redirect(\Filament\Facades\Filament::getUrl());
    }
}
