<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('insureds', function (Blueprint $table) {
            $table->string('document_type', 20)->default('cedula')->after('last_name');
            $table->dropUnique(['document_id']);
            $table->unique(['document_type', 'document_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insureds', function (Blueprint $table) {
            $table->dropUnique(['document_type', 'document_id']);
            $table->unique('document_id');
            $table->dropColumn('document_type');
        });
    }
};
