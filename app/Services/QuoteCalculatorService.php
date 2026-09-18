<?php

namespace App\Services;

use App\Enums\Region;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class QuoteCalculatorService
{
    public const DAILY_RATE = 3.00;

    /**
     * @return array{
     *     days: int,
     *     daily_rate: float,
     *     region: string,
     *     surcharge_percentage: float,
     *     subtotal: float,
     *     surcharge_amount: float,
     *     total: float,
     * }
     */
    public function calculate(CarbonInterface $departureDate, CarbonInterface $returnDate, Region $region): array
    {
        if ($returnDate->lessThan($departureDate)) {
            throw new InvalidArgumentException('The return date cannot be before the departure date.');
        }

        // Both the departure and return dates are covered by the policy.
        $days = (int) $departureDate->startOfDay()->diffInDays($returnDate->startOfDay()) + 1;

        $subtotal = round(self::DAILY_RATE * $days, 2);
        $surchargePercentage = $region->surchargePercentage();
        $surchargeAmount = round($subtotal * $surchargePercentage / 100, 2);

        return [
            'days' => $days,
            'daily_rate' => self::DAILY_RATE,
            'region' => $region->value,
            'surcharge_percentage' => $surchargePercentage,
            'subtotal' => $subtotal,
            'surcharge_amount' => $surchargeAmount,
            'total' => round($subtotal + $surchargeAmount, 2),
        ];
    }
}
