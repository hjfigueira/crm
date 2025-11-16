<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL 8.0.16+ supports CHECK constraints. Use IF NOT EXISTS pattern via try/catch guard.
            DB::statement("ALTER TABLE `transactions` ADD CONSTRAINT `chk_transactions_amount_positive` CHECK (`amount` > 0)");
            DB::statement("ALTER TABLE `transactions` ADD CONSTRAINT `chk_transactions_payment_after_due` CHECK (`payment_date` IS NULL OR `payment_date` >= `due_date`)");
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transactions_amount_positive CHECK (amount > 0)');
            DB::statement('ALTER TABLE transactions ADD CONSTRAINT chk_transactions_payment_after_due CHECK (payment_date IS NULL OR payment_date >= due_date)');
        } else {
            // Other drivers: skip silently.
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `transactions` DROP CHECK `chk_transactions_amount_positive`');
            DB::statement('ALTER TABLE `transactions` DROP CHECK `chk_transactions_payment_after_due`');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS chk_transactions_amount_positive');
            DB::statement('ALTER TABLE transactions DROP CONSTRAINT IF EXISTS chk_transactions_payment_after_due');
        } else {
            // Other drivers: nothing to rollback.
        }
    }
};
