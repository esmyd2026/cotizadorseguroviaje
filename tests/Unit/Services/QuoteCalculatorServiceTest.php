<?php

use App\Enums\Region;
use App\Services\QuoteCalculatorService;
use Illuminate\Support\Carbon;

it('matches the reference example for an eleven-day trip to europe', function () {
    $result = app(QuoteCalculatorService::class)->calculate(
        Carbon::parse('2026-10-12'),
        Carbon::parse('2026-10-22'),
        Region::Europe,
    );

    expect($result)->toBe([
        'days' => 11,
        'daily_rate' => 3.00,
        'region' => 'Europe',
        'surcharge_percentage' => 20.0,
        'subtotal' => 33.00,
        'surcharge_amount' => 6.60,
        'total' => 39.60,
    ]);
});

it('counts both the departure and return day as part of the trip', function () {
    $result = app(QuoteCalculatorService::class)->calculate(
        Carbon::parse('2026-01-01'),
        Carbon::parse('2026-01-01'),
        Region::SouthAmerica,
    );

    expect($result['days'])->toBe(1);
});

it('rejects a return date earlier than the departure date', function () {
    app(QuoteCalculatorService::class)->calculate(
        Carbon::parse('2026-01-10'),
        Carbon::parse('2026-01-01'),
        Region::Africa,
    );
})->throws(InvalidArgumentException::class);

it('applies the correct surcharge percentage for each region', function (Region $region, float $percentage) {
    $result = app(QuoteCalculatorService::class)->calculate(
        Carbon::parse('2026-06-01'),
        Carbon::parse('2026-06-10'),
        $region,
    );

    expect($result['surcharge_percentage'])->toBe($percentage);
})->with([
    'South America' => [Region::SouthAmerica, 0.0],
    'North America' => [Region::NorthAmerica, 15.0],
    'Europe' => [Region::Europe, 20.0],
    'Asia' => [Region::Asia, 25.0],
    'Africa' => [Region::Africa, 20.0],
    'Oceania' => [Region::Oceania, 25.0],
]);
