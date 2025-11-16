<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use App\Models\Tenant;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/tenant/switch', function (Request $request) {
    $request->validate([
        'tenant_id' => ['required', 'integer'],
    ]);

    $user = $request->user();

    if (! $user) {
        abort(403);
    }

    $tenantId = (int) $request->input('tenant_id');

    $isSuper = method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));

    $isAllowedTarget = $isSuper
        ? Tenant::query()->whereKey($tenantId)->exists()
        : $user->tenants()->whereKey($tenantId)->exists();

    abort_unless($isAllowedTarget, 403);

    session(['tenant_id' => $tenantId]);

    return Redirect::back();
})->name('tenant.switch')->middleware('auth');
