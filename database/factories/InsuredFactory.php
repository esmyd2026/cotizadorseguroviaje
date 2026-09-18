<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Models\Insured;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insured>
 */
class InsuredFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'document_type' => DocumentType::Passport,
            'document_id' => fake()->unique()->bothify('??######'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+593'.fake()->numerify('#########'),
            'birth_date' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
        ];
    }
}
