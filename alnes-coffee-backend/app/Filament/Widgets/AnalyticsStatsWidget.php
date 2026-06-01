<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AnalyticsStatsWidget extends BaseWidget
{
    protected static bool $isDiscovered = false;
    protected static ?int $sort            = 1;
    protected int|string|array $columnSpan = 'full';

    public string $dateFrom = '';
    public string $dateTo   = '';

    protected function getHeading(): ?string
    {
        return '📊 Statistik ' . now()->translatedFormat('F Y');
    }

    protected function getStats(): array
    {
        $from = $this->dateFrom
            ? Carbon::parse($this->dateFrom)->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $this->dateTo
            ? Carbon::parse($this->dateTo)->endOfDay()
            : now()->endOfDay();

        $totalRevenue = Order::whereBetween('ordered_at', [$from, $to])
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('grand_total');

        $totalOrders = Order::whereBetween('ordered_at', [$from, $to])
            ->where('payment_status', PaymentStatus::Paid)
            ->count();

        $avgOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $totalItems = OrderItem::whereHas('order', fn ($q) =>
            $q->whereBetween('ordered_at', [$from, $to])
              ->where('payment_status', PaymentStatus::Paid)
        )->sum('qty');

        return [
            Stat::make('Total Revenue', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Total transaksi terbayar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart(
                    Order::selectRaw('DATE(ordered_at) as date, SUM(grand_total) as total')
                        ->whereBetween('ordered_at', [$from, $to])
                        ->where('payment_status', PaymentStatus::Paid)
                        ->groupBy('date')
                        ->orderBy('date')
                        ->pluck('total')
                        ->toArray()
                ),

            Stat::make('Total Order', $totalOrders)
                ->description('Order terbayar')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),

            Stat::make('Rata-rata Order', 'Rp ' . number_format($avgOrder, 0, ',', '.'))
                ->description('Per transaksi')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),

            Stat::make('Total Item Terjual', $totalItems)
                ->description('Item terjual')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),
        ];
    }
}