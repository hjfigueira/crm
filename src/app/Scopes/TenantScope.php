<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Super Admins bypass tenant scoping entirely
        if (auth()->check() && method_exists(auth()->user(), 'hasRole') && auth()->user()->hasRole('Super Admin')) {
            return;
        }

        $tenant = app()->bound('currentTenant') ? app('currentTenant') : null;

        if ($tenant !== null) {
            $builder->where($model->getTable() . '.tenant_id', $tenant->id);
        }
    }
}
