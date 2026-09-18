<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Defense-in-depth against a buggy or compromised write path producing a
     * negative price or a zero/negative-day quote; QuoteCalculatorService
     * already guarantees these invariants, this backs them at the DB layer.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE quotes ADD CONSTRAINT chk_quotes_days_positive CHECK (days >= 1)');
        DB::statement('ALTER TABLE quotes ADD CONSTRAINT chk_quotes_amounts_non_negative CHECK (
            daily_rate >= 0
            AND surcharge_percentage >= 0
            AND subtotal >= 0
            AND surcharge_amount >= 0
            AND total >= 0
        )');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE quotes DROP CONSTRAINT chk_quotes_days_positive');
        DB::statement('ALTER TABLE quotes DROP CONSTRAINT chk_quotes_amounts_non_negative');
    }
};
