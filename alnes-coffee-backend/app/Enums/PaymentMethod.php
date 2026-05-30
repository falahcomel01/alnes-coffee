<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case QRIS     = 'qris';
    case Cash     = 'cash';
    case Transfer = 'transfer';
    case EWallet  = 'ewallet';

    public function label(): string
    {
        return match($this) {
            self::QRIS     => 'QRIS',
            self::Cash     => 'Tunai',
            self::Transfer => 'Transfer Bank',
            self::EWallet  => 'E-Wallet',
        };
    }

    public function isDigital(): bool
    {
        return in_array($this, [self::QRIS, self::EWallet, self::Transfer]);
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}