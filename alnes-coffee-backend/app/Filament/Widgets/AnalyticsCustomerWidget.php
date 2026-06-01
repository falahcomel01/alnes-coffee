<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnalyticsCustomerWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 1;

    public static function getSort(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $total        = Customer::count();
        $newThisMonth = Customer::whereMonth('created_at', now()->month)->count();
        $bronze       = Customer::where('tier', 'bronze')->count();
        $silver       = Customer::where('tier', 'silver')->count();
        $gold         = Customer::where('tier', 'gold')->count();
        $platinum     = Customer::where('tier', 'platinum')->count();

        return [
            Stat::make('Total Customer', $total)
                ->description("+{$newThisMonth} baru bulan ini")
                ->descriptionIcon('heroicon-o-user-plus')
                ->color('success'),

            Stat::make('🥉 Bronze / 🥈 Silver', "{$bronze} / {$silver}")
                ->description('Customer tier bawah')
                ->color('warning'),

            Stat::make('🥇 Gold / 💎 Platinum', "{$gold} / {$platinum}")
                ->description('Customer tier atas')
                ->color('primary'),
        ];
    }
}