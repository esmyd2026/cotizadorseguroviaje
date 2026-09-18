<?php

namespace App\Services;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberService
{
    private PhoneNumberUtil $util;

    public function __construct()
    {
        $this->util = PhoneNumberUtil::getInstance();
    }

    public function dialCodeFor(string $regionCode): ?string
    {
        $callingCode = $this->util->getCountryCodeForRegion($regionCode);

        return $callingCode > 0 ? "+{$callingCode}" : null;
    }

    /**
     * @return array{min: int, max: int}|null
     */
    public function possibleLengthFor(string $regionCode): ?array
    {
        $metadata = $this->util->getMetadataForRegion($regionCode);
        $lengths = $metadata?->getGeneralDesc()?->getPossibleLength();

        if (! $lengths) {
            return null;
        }

        return [
            'min' => min($lengths),
            'max' => max($lengths),
        ];
    }

    public function isValidNumber(string $nationalNumber, string $regionCode): bool
    {
        try {
            $parsed = $this->util->parse($nationalNumber, $regionCode);
        } catch (NumberParseException) {
            return false;
        }

        return $this->util->isValidNumber($parsed);
    }

    public function toE164(string $nationalNumber, string $regionCode): ?string
    {
        try {
            $parsed = $this->util->parse($nationalNumber, $regionCode);
        } catch (NumberParseException) {
            return null;
        }

        if (! $this->util->isValidNumber($parsed)) {
            return null;
        }

        return $this->util->format($parsed, PhoneNumberFormat::E164);
    }
}
