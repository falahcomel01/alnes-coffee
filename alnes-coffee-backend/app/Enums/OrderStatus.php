<?php
// app/Enums/OrderStatus.php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Confirmed = 'confirmed';
    case Cooking   = 'cooking';   // ← ganti dari Preparing = 'preparing'
    case Ready     = 'ready';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'Menunggu',
            self::Confirmed => 'Dikonfirmasi',
            self::Cooking   => 'Dimasak',     // ← ganti
            self::Ready     => 'Siap',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending   => 'warning',
            self::Confirmed => 'info',
            self::Cooking   => 'warning',     // ← ganti
            self::Ready     => 'success',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}