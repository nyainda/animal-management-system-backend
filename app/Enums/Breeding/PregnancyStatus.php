<?php

namespace App\Enums\Breeding;

enum PregnancyStatus: string
{
    case NotPregnant = 'not_pregnant';
    case Pregnant = 'pregnant';
    case Delivered = 'delivered';
    case Miscarriage = 'miscarriage';

    public function label(): string
    {
        return match ($this) {
            self::NotPregnant => 'Not Pregnant',
            self::Pregnant => 'Pregnant',
            self::Delivered => 'Delivered',
            self::Miscarriage => 'Miscarriage',
        };
    }
}
