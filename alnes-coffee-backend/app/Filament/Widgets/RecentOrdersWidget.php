<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Order Terbaru Hari Ini';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::with(['table', 'items'])
                    ->today()
                    ->orderByDesc('ordered_at')
                    ->limit(10)
            )
            ->poll('10s')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->label('Customer'),

                TextColumn::make('table.table_number')
                    ->label('Meja')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('order_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR'),

                TextColumn::make('ordered_at')
                    ->label('Waktu')
                    ->dateTime('H:i')
                    ->sortable(),
            ])
            ->actions([
                Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->size('sm')
                    ->visible(fn ($record) => $record->order_status->value === 'pending')
                    ->action(fn ($record) => $record->update(['order_status' => 'confirmed']))
                    ->requiresConfirmation(),

                Action::make('cooking')
                    ->label('Masak')
                    ->icon('heroicon-o-fire')
                    ->color('warning')
                    ->size('sm')
                    ->visible(fn ($record) => $record->order_status->value === 'confirmed')
                    ->action(fn ($record) => $record->update(['order_status' => 'cooking']))
                    ->requiresConfirmation(),

                Action::make('ready')
                    ->label('Siap')
                    ->icon('heroicon-o-bell')
                    ->color('success')
                    ->size('sm')
                    ->visible(fn ($record) => $record->order_status->value === 'cooking')
                    ->action(fn ($record) => $record->update(['order_status' => 'ready']))
                    ->requiresConfirmation(),
            ]);
    }
}