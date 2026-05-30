<?php

namespace App\Enums;

enum CategoryType: string
{
    case Food      = 'food';
    case Beverages = 'beverages';

    public function label(): string
    {
        return match($this) {
            self::Food      => 'Makanan',
            self::Beverages => 'Minuman',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}