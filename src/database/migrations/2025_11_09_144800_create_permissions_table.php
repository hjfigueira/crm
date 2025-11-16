<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            // Multi-tenant support: nullable to allow global permissions when null
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->timestamps();

            // In a multi-tenant app, permission names are unique per (tenant, guard)
            $table->unique(['name', 'guard_name', 'tenant_id'], 'permissions_name_guard_tenant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
