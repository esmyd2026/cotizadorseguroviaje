<?php

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Enums\Region;
use App\Models\Insured;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dailyRate = 3.00;
        $departureDate = fake()->dateTimeBetween('+1 week', '+3 months');
        $days = fake()->numberBetween(5, 21);
        $returnDate = (clone $departureDate)->modify("+{$days} days");

        $region = fake()->randomElement(Region::cases());
        $subtotal = round($dailyRate * $days, 2);
        $surchargeAmount = round($subtotal * $region->surchargePercentage() / 100, 2);

        return [
            'insured_id' => Insured::factory(),
            'reference' => 'SEG-'.now()->year.'-'.fake()->unique()->numerify('######'),
            'trip_type' => 'direct',
            'destination_country_code' => fake()->countryCode(),
            'destination_country_name' => fake()->country(),
            'destinations' => null,
            'region' => $region->value,
            'departure_date' => $departureDate->format('Y-m-d'),
            'return_date' => $returnDate->format('Y-m-d'),
            'days' => $days,
            'daily_rate' => $dailyRate,
            'surcharge_percentage' => $region->surchargePercentage(),
            'subtotal' => $subtotal,
            'surcharge_amount' => $surchargeAmount,
            'total' => $subtotal + $surchargeAmount,
            'status' => QuoteStatus::Quoted,
            'contracted_at' => null,
        ];
    }

    public function contracted(): static
    {
        return $this->state(fn () => [
            'status' => QuoteStatus::Contracted,
            'contracted_at' => now(),
        ]);
    }
}
