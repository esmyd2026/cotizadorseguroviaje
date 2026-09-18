<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforces valid Quote statuses at the database layer, independent of the
     * PHP enum cast, so a raw query or a compromised app layer cannot write an
     * arbitrary value into this column. Not portable to SQLite's ALTER TABLE,
     * which is only used for the test database.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE quotes ADD CONSTRAINT chk_quotes_status CHECK (status IN ('quoted', 'contracted'))");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE quotes DROP CONSTRAINT chk_quotes_status');
    }
};
