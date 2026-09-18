<?php

namespace App\Services;

class EcuadorianIdentityService
{
    public function isValidCedula(string $value): bool
    {
        if (! preg_match('/^\d{10}$/', $value)) {
            return false;
        }

        $province = (int) substr($value, 0, 2);
        $thirdDigit = (int) $value[2];

        if ($province < 1 || $province > 24 || $thirdDigit > 5) {
            return false;
        }

        $sum = 0;

        for ($index = 0; $index < 9; $index++) {
            $digit = (int) $value[$index];
            $product = $digit * ($index % 2 === 0 ? 2 : 1);
            $sum += $product > 9 ? $product - 9 : $product;
        }

        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit === (int) $value[9];
    }
}
