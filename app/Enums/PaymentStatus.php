<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Approved = 'approved';
    case Declined = 'declined';

    public function label(): string
    {
        return match ($this) {
            self::Approved => 'Aprobado',
            self::Declined => 'Rechazado',
        };
    }
}
