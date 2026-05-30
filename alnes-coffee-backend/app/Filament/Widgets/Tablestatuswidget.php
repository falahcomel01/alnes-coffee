<?php

namespace App\Filament\Widgets;

use App\Models\CafeTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TableStatusWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $tables        = CafeTable::all();
        $total         = $tables->count();
        $available     = $tables->filter(fn ($t) => $t->isAvailable())->count();
        $occupied      = $tables->filter(fn ($t) => !$t->isAvailable())->count();
        $occupancyRate = $total > 0 ? round(($occupied / $total) * 100) : 0;

        return [
            Stat::make('Total Meja', $total)
                ->description('Semua meja terdaftar')
                ->color('gray')
                ->icon('heroicon-o-square-3-stack-3d'),

            Stat::make('🟢 Tersedia', $available)
                ->description('Meja siap digunakan')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('🔴 Terisi', $occupied)
                ->description("Occupancy {$occupancyRate}%")
                ->color($occupied > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-user-group'),
        ];
    }
}