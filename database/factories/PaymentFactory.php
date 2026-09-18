<?php

namespace Database\Factories;

use App\Enums\CardBrand;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote_id' => Quote::factory(),
            'idempotency_key' => fake()->uuid(),
            'reference' => 'PAY-'.now()->format('Ymd').'-'.fake()->unique()->regexify('[A-Z0-9]{10}'),
            'status' => PaymentStatus::Approved,
            'card_brand' => CardBrand::Visa,
            'card_last_four' => '4242',
            'amount' => 39.60,
            'currency' => 'USD',
            'authorization_code' => fake()->regexify('[A-Z0-9]{8}'),
            'failure_code' => null,
            'paid_at' => now(),
        ];
    }
}
