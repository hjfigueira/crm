<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            if (! Schema::hasColumn('entities', 'tenant_id')) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->constrained('tenants')
                    ->nullOnDelete()
                    ->after('id');
                // Note: foreignId creates an index automatically.
            }
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            if (Schema::hasColumn('entities', 'tenant_id')) {
                $table->dropConstrainedForeignId('tenant_id');
            }
        });
    }
};
