<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AnalyticsPeakHoursWidget extends ChartWidget
{
    protected ?string $heading   = '⏰ Peak Hours — Jam Tersibuk';
    protected ?string $maxHeight = '200px';
    protected static ?int $sort  = 4;
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

        $peakData = Order::selectRaw('HOUR(ordered_at) as hour, COUNT(*) as total')
            ->whereBetween('ordered_at', [$from, $to])
            ->where('payment_status', PaymentStatus::Paid)
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        $hours  = array_map(fn ($i) => str_pad($i, 2, '0', STR_PAD_LEFT) . ':00', range(0, 23));
        $values = array_map(fn ($i) => $peakData[$i] ?? 0, range(0, 23));
        $max    = max($values) ?: 1;

        return [
            'datasets' => [[
                'label'           => 'Jumlah Order',
                'data'            => $values,
                'backgroundColor' => array_map(
                    fn ($v) => $v === $max && $v > 0 ? '#C8872A' : 'rgba(200,135,42,0.25)',
                    $values
                ),
                'borderRadius' => 4,
            ]],
            'labels' => $hours,
        ];
    }

    protected function getType(): string { return 'bar'; }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => [
                'y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]],
                'x' => ['ticks' => ['font' => ['size' => 10]]],
            ],
        ];
    }
}