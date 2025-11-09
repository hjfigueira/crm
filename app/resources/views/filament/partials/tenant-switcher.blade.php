@php
    $user = auth()->user();
    $isSuper = $user && method_exists($user, 'hasRole') && ($user->hasRole('Super Admin', null) || $user->hasRole('Super Admin'));
    $tenants = $isSuper
        ? \App\Models\Tenant::query()->pluck('name', 'id')
        : ($user?->tenants()->pluck('name', 'id') ?? collect());
    $currentTenantId = session('tenant_id');
@endphp

@if($user && $tenants->isNotEmpty())
    <form method="POST" action="{{ route('tenant.switch') }}" class="fi-topbar-item">
        @csrf
        <label class="sr-only" for="fi-tenant-switcher">Tenant</label>
        <select id="fi-tenant-switcher"
                name="tenant_id"
                class="fi-input rounded-md border-gray-300 text-sm"
                x-on:change="$el.form.submit()">
            @foreach($tenants as $id => $name)
                <option value="{{ $id }}" @selected((int) $currentTenantId === (int) $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </form>
@endif
