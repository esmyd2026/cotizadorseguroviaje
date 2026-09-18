<?php

namespace App\Services;

use App\Enums\CardBrand;

class CardValidationService
{
    public function normalize(string $number): string
    {
        return preg_replace('/\D+/', '', $number) ?? '';
    }

    public function brand(string $number): ?CardBrand
    {
        $number = $this->normalize($number);

        if (strlen($number) !== 16) {
            return null;
        }

        if (str_starts_with($number, '4')) {
            return CardBrand::Visa;
        }

        $firstTwoDigits = (int) substr($number, 0, 2);
        $firstFourDigits = (int) substr($number, 0, 4);

        if (($firstTwoDigits >= 51 && $firstTwoDigits <= 55)
            || ($firstFourDigits >= 2221 && $firstFourDigits <= 2720)) {
            return CardBrand::Mastercard;
        }

        return null;
    }

    public function passesLuhn(string $number): bool
    {
        $number = $this->normalize($number);

        if ($number === '' || ! ctype_digit($number)) {
            return false;
        }

        $sum = 0;
        $parity = strlen($number) % 2;

        foreach (str_split($number) as $index => $character) {
            $digit = (int) $character;

            if ($index % 2 === $parity) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
