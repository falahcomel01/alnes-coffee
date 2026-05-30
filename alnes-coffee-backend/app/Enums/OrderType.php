<?php

namespace App\Enums;

enum OrderType: string
{
    case DineIn   = 'dine_in';
    case Takeaway = 'takeaway';

    public function label(): string
    {
        return match($this) {
            self::DineIn   => 'Makan di Tempat',
            self::Takeaway => 'Bawa Pulang',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}