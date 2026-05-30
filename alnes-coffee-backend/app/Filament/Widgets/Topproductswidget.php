<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsWidget extends ChartWidget
{
    protected ?string $heading        = 'Top 5 Produk Hari Ini';
    protected ?string $maxHeight      = '280px';
    protected static ?int $sort       = 3;
    protected int | string | array $columnSpan = 'full';
    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $products = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereDate('orders.ordered_at', today())
            ->whereNull('orders.deleted_at')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {
            return [
                'datasets' => [['data' => [1], 'backgroundColor' => ['#374151']]],
                'labels'   => ['Belum ada pesanan'],
            ];
        }

        $colors = [
            '#C8872A',
            '#3B82F6',
            '#10B981',
            '#8B5CF6',
            '#F43F5E',
        ];

        return [
            'datasets' => [
                [
                    'data'            => $products->pluck('total_qty')->toArray(),
                    'backgroundColor' => $colors,
                    'borderWidth'     => 2,
                    'borderColor'     => '#1f2937',
                    'hoverOffset'     => 6,
                ],
            ],
            'labels' => $products->map(fn ($p) =>
                $p->name . ' (' . $p->total_qty . ' terjual · Rp' . number_format($p->total_revenue, 0, ',', '.') . ')'
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels'   => [
                        'padding'   => 16,
                        'font'      => ['size' => 12],
                        'boxWidth'  => 12,
                        'boxHeight' => 12,
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [],
                ],
            ],
            'cutout' => '65%',
        ];
    }
}