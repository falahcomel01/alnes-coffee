<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid   = 'unpaid';
    case Pending  = 'pending';
    case Paid     = 'paid';
    case Failed   = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::Unpaid   => 'Belum Bayar',
            self::Pending  => 'Menunggu Pembayaran',
            self::Paid     => 'Lunas',
            self::Failed   => 'Gagal',
            self::Refunded => 'Dikembalikan',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Unpaid   => 'gray',
            self::Pending  => 'warning',
            self::Paid     => 'success',
            self::Failed   => 'danger',
            self::Refunded => 'info',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}