<?php

namespace App\Enums;

enum DocumentType: string
{
    case Cedula = 'cedula';
    case Passport = 'passport';

    public function label(): string
    {
        return match ($this) {
            self::Cedula => 'Cédula ecuatoriana',
            self::Passport => 'Pasaporte',
        };
    }
}
