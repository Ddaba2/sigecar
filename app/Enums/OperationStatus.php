<?php

namespace App\Enums;

enum OperationStatus: string
{
    case SousDouane = 'sous_douane';
    case Acquitte   = 'acquitte';
    case Confirmed  = 'confirmed';
    case Completed  = 'completed';
    case Termine    = 'termine';

    public function label(): string
    {
        return match ($this) {
            self::SousDouane => 'Sous douane',
            self::Acquitte   => 'Acquitté',
            self::Confirmed  => 'Acquitté',
            self::Completed  => 'Acquitté',
            self::Termine    => 'Acquitté',
        };
    }

    public function isAcquitte(): bool
    {
        return in_array($this, [self::Acquitte, self::Confirmed, self::Completed, self::Termine]);
    }
}
