<?php

namespace App\Filament\Pages\Auth;

use App\Providers\LoginRedirect;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();
        return new LoginRedirect();
    }
}
