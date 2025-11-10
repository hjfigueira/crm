<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            // Core attributes
            $table->string('type')->default('person')->index(); // person | company
            $table->string('name')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();

            // Address
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_district')->nullable();
            $table->string('address_city')->nullable()->index();
            $table->string('address_state', 2)->nullable()->index();
            $table->string('address_postal_code', 20)->nullable()->index();
            $table->string('address_country', 2)->nullable()->default('BR')->index();

            // Company specific (Pessoa Jurídica)
            $table->string('company_legal_name')->nullable()->index();
            $table->string('company_trade_name')->nullable();
            $table->string('company_cnpj', 20)->nullable()->index();
            $table->string('company_state_registration')->nullable();
            $table->string('company_municipal_registration')->nullable();
            $table->string('company_website')->nullable();

            // Person specific (Pessoa Física)
            $table->string('person_first_name')->nullable()->index();
            $table->string('person_last_name')->nullable()->index();
            $table->string('person_cpf', 20)->nullable()->index();
            $table->string('person_rg')->nullable();
            $table->date('person_birth_date')->nullable();
            $table->string('person_gender', 20)->nullable();

            // Extra
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            $table->dropColumn([
                'type',
                'name',
                'email',
                'phone',
                'mobile',
                'address_line1',
                'address_line2',
                'address_number',
                'address_district',
                'address_city',
                'address_state',
                'address_postal_code',
                'address_country',
                'company_legal_name',
                'company_trade_name',
                'company_cnpj',
                'company_state_registration',
                'company_municipal_registration',
                'company_website',
                'person_first_name',
                'person_last_name',
                'person_cpf',
                'person_rg',
                'person_birth_date',
                'person_gender',
                'notes',
            ]);
        });
    }
};
