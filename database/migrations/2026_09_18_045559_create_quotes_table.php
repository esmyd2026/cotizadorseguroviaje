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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insured_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('destination_country_code', 2);
            $table->string('destination_country_name');
            $table->string('region');
            $table->date('departure_date');
            $table->date('return_date');
            $table->unsignedInteger('days');
            $table->decimal('daily_rate', 8, 2);
            $table->decimal('surcharge_percentage', 5, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('surcharge_amount', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('status')->default('quoted');
            $table->timestamp('contracted_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
