<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsTopProductsWidget extends ChartWidget
{
    protected ?string $heading   = '🏆 Top 10 Produk';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort  = 5;
    protected int|string|array $columnSpan = 'full';

    public string $dateFrom = '';
    public string $dateTo   = '';

    protected function getData(): array
    {
        $from = $this->dateFrom
            ? Carbon::parse($this->dateFrom)->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $to = $this->dateTo
            ? Carbon::parse($this->dateTo)->endOfDay()
            : now()->endOfDay();

        $products = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.ordered_at', [$from, $to])
            ->where('orders.payment_status', PaymentStatus::Paid)
            ->whereNull('orders.deleted_at')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            return [
                'datasets' => [['data' => [1], 'backgroundColor' => ['#374151']]],
                'labels'   => ['Belum ada pesanan'],
            ];
        }

        $colors = ['#C8872A','#3B82F6','#10B981','#8B5CF6','#F43F5E','#F59E0B','#06B6D4','#84CC16','#EC4899','#14B8A6'];

        return [
            'datasets' => [[
                'data'            => $products->pluck('total_qty')->toArray(),
                'backgroundColor' => array_slice($colors, 0, $products->count()),
                'borderWidth'     => 2,
                'borderColor'     => '#1f2937',
                'hoverOffset'     => 6,
            ]],
            'labels' => $products->map(fn ($p) =>
                $p->name . ' (' . $p->total_qty . 'x · Rp' . number_format($p->total_revenue, 0, ',', '.') . ')'
            )->toArray(),
        ];
    }

    protected function getType(): string { return 'doughnut'; }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels'   => ['padding' => 16, 'font' => ['size' => 12], 'boxWidth' => 12],
                ],
            ],
            'cutout' => '65%',
        ];
    }
}