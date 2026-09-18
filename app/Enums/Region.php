<?php

namespace App\Enums;

enum Region: string
{
    case SouthAmerica = 'South America';
    case NorthAmerica = 'North America';
    case Europe = 'Europe';
    case Asia = 'Asia';
    case Africa = 'Africa';
    case Oceania = 'Oceania';

    public function surchargePercentage(): float
    {
        return match ($this) {
            self::SouthAmerica => 0.0,
            self::NorthAmerica => 15.0,
            self::Europe => 20.0,
            self::Asia => 25.0,
            self::Africa => 20.0,
            self::Oceania => 25.0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SouthAmerica => 'Sudamérica',
            self::NorthAmerica => 'Norteamérica',
            self::Europe => 'Europa',
            self::Asia => 'Asia',
            self::Africa => 'África',
            self::Oceania => 'Oceanía',
        };
    }
}
