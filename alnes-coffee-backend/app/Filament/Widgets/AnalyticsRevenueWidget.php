<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AnalyticsRevenueWidget extends ChartWidget
{
    protected ?string $heading   = '📈 Revenue Harian';
    protected ?string $maxHeight = '250px';
    protected static ?int $sort  = 2;
    protected int|string|array $columnSpan = 2;

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

        $data = Order::selectRaw('DATE(ordered_at) as date, SUM(grand_total) as total')
            ->whereBetween('ordered_at', [$from, $to])
            ->where('payment_status', PaymentStatus::Paid)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [[
                'label'                => 'Revenue (Rp)',
                'data'                 => $data->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
                'borderColor'          => '#C8872A',
                'backgroundColor'      => 'rgba(200,135,42,0.08)',
                'fill'                 => true,
                'tension'              => 0.4,
                'pointRadius'          => 3,
                'pointBackgroundColor' => '#C8872A',
            ]],
            'labels' => $data->pluck('date')->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string { return 'line'; }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales'  => ['y' => ['beginAtZero' => true]],
        ];
    }
}