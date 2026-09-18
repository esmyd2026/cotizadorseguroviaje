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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->uuid('idempotency_key');
            $table->string('reference')->unique();
            $table->string('status', 20);
            $table->string('card_brand', 20);
            $table->string('card_last_four', 4);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('authorization_code', 20)->nullable();
            $table->string('failure_code', 50)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['quote_id', 'idempotency_key']);
            $table->index(['quote_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
