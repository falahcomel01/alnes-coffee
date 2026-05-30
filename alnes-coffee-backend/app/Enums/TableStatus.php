<?php

namespace App\Enums;

enum TableStatus: string
{
    case Available = 'available';
    case Occupied  = 'occupied';

    public function label(): string
    {
        return match($this) {
            self::Available => 'Tersedia',
            self::Occupied  => 'Terisi',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Available => 'success',
            self::Occupied  => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}