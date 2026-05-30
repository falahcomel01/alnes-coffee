<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin   = 'admin';
    case Cashier = 'cashier';
    case Kitchen = 'kitchen';

    public function label(): string
    {
        return match($this) {
            self::Admin   => 'Administrator',
            self::Cashier => 'Kasir',
            self::Kitchen => 'Dapur',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Admin   => 'danger',
            self::Cashier => 'warning',
            self::Kitchen => 'success',
        };
    }

    public function permissions(): array
    {
        return match($this) {
            self::Admin => [
                'manage-products', 'manage-categories', 'manage-promos',
                'manage-tables', 'manage-users', 'manage-settings',
                'view-reports', 'manage-orders', 'manage-payments',
            ],
            self::Cashier => ['view-orders', 'manage-payments', 'confirm-orders'],
            self::Kitchen => ['view-orders', 'update-order-status'],
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}