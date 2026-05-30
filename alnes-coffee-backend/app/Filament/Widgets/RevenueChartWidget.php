<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue 7 Hari Terakhir';
    protected ?string $maxHeight = '250px';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 2;

    protected function getData(): array
    {
        $data   = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date     = Carbon::today()->subDays($i);
            $labels[] = $date->format('D, d M');

            $revenue = Order::whereDate('ordered_at', $date)
                ->where('payment_status', PaymentStatus::Paid)
                ->sum('grand_total');

            $data[] = (float) $revenue;
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (Rp)',
                    'data'            => $data,
                    'borderColor'     => '#C8872A',
                    'backgroundColor' => 'rgba(200, 135, 42, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => ['y' => ['beginAtZero' => true]],
        ];
    }
}