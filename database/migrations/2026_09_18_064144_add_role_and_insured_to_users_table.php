<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->after('email');
            $table->foreignId('insured_id')->nullable()->unique()->after('role')
                ->constrained()->nullOnDelete();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ('admin', 'customer'))");
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT chk_users_role');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('insured_id');
            $table->dropColumn('role');
        });
    }
};
