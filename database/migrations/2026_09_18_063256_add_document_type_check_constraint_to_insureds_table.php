<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Same rationale as the quotes.status check constraint: enforce valid
     * document types at the database layer, not only via the PHP enum cast.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE insureds ADD CONSTRAINT chk_insureds_document_type CHECK (document_type IN ('cedula', 'passport'))");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE insureds DROP CONSTRAINT chk_insureds_document_type');
    }
};
