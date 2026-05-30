<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $todayRevenue = Order::today()
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('grand_total');

        $pendingOrders = Order::today()
            ->where('order_status', OrderStatus::Pending)
            ->count();

        $activeOrders = Order::today()
            ->whereIn('order_status', [
                OrderStatus::Confirmed,
                OrderStatus::Cooking,
                OrderStatus::Ready,
            ])
            ->count();

        $totalOrdersToday = Order::today()->count();

        return [
            Stat::make('Revenue Hari Ini', 'Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('Total transaksi terbayar hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart(
                    Order::selectRaw('DATE(ordered_at) as date, SUM(grand_total) as total')
                        ->where('payment_status', PaymentStatus::Paid)
                        ->where('ordered_at', '>=', now()->subDays(7))
                        ->groupBy('date')
                        ->orderBy('date')
                        ->pluck('total')
                        ->toArray()
                ),

            Stat::make('Order Pending', $pendingOrders)
                ->description('Menunggu konfirmasi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('Order Aktif', $activeOrders)
                ->description('Sedang diproses dapur')
                ->descriptionIcon('heroicon-m-fire')
                ->color($activeOrders > 0 ? 'info' : 'gray'),

            Stat::make('Total Order Hari Ini', $totalOrdersToday)
                ->description('Semua order masuk hari ini')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
        ];
    }
}