<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AnalyticsStatsWidget;
use App\Filament\Widgets\AnalyticsRevenueWidget;
use App\Filament\Widgets\AnalyticsTopProductsWidget;
use App\Filament\Widgets\AnalyticsPeakHoursWidget;
use App\Filament\Widgets\AnalyticsCustomerWidget;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class Analytics extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationLabel = 'Analytics';
    protected static ?string $title           = 'Analytics';
    protected static ?int    $navigationSort  = 1;

    public string $dateFrom = '';
    public string $dateTo   = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo   = now()->format('Y-m-d');
    }

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-chart-bar';
    }

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public function getHeaderWidgets(): array
    {
        return [
            AnalyticsStatsWidget::class,
            AnalyticsRevenueWidget::class,
            AnalyticsCustomerWidget::class,
            AnalyticsPeakHoursWidget::class,
            AnalyticsTopProductsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }

    public function getWidgetData(): array
    {
        return [
            'dateFrom' => $this->dateFrom,
            'dateTo'   => $this->dateTo,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // ── Filter Tanggal ──────────────────────────────────
            Action::make('filter')
                ->label('Filter Tanggal')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->form([
                    DatePicker::make('dateFrom')
                        ->label('Dari Tanggal')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('dateTo')
                        ->label('Sampai Tanggal')
                        ->default(now())
                        ->required(),
                ])
                ->fillForm(fn () => [
                    'dateFrom' => $this->dateFrom,
                    'dateTo'   => $this->dateTo,
                ])
                ->action(function (array $data): void {
                    $this->dateFrom = $data['dateFrom'];
                    $this->dateTo   = $data['dateTo'];
                }),

            Action::make('today')
                ->label('Hari Ini')
                ->color('gray')
                ->action(function (): void {
                    $this->dateFrom = now()->format('Y-m-d');
                    $this->dateTo   = now()->format('Y-m-d');
                }),

            Action::make('this_week')
                ->label('7 Hari')
                ->color('gray')
                ->action(function (): void {
                    $this->dateFrom = now()->subDays(6)->format('Y-m-d');
                    $this->dateTo   = now()->format('Y-m-d');
                }),

            Action::make('this_month')
                ->label('Bulan Ini')
                ->color('primary')
                ->action(function (): void {
                    $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
                    $this->dateTo   = now()->format('Y-m-d');
                }),

            // ── Export ─────────────────────────────────────────
            Action::make('export_summary')
                ->label('Export Summary')
                ->icon('heroicon-o-document-chart-bar')
                ->color('warning')
                ->url(fn () => url('/api/v1/export/csv') . '?' . http_build_query([
                    'date_from' => $this->dateFrom,
                    'date_to'   => $this->dateTo,
                    'type'      => 'summary',
                ]))
                ->openUrlInNewTab(),

            Action::make('export_orders')
                ->label('Export Order')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => url('/api/v1/export/csv') . '?' . http_build_query([
                    'date_from' => $this->dateFrom,
                    'date_to'   => $this->dateTo,
                    'type'      => 'orders',
                ]))
                ->openUrlInNewTab(),

            Action::make('export_products')
                ->label('Export Produk')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url(fn () => url('/api/v1/export/csv') . '?' . http_build_query([
                    'date_from' => $this->dateFrom,
                    'date_to'   => $this->dateTo,
                    'type'      => 'products',
                ]))
                ->openUrlInNewTab(),
        ];
    }
}