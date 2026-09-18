<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case Quoted = 'quoted';
    case Contracted = 'contracted';

    public function label(): string
    {
        return match ($this) {
            self::Quoted => 'Cotizado',
            self::Contracted => 'Contratado',
        };
    }
}
