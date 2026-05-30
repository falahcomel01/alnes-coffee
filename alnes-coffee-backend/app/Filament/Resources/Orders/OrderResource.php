<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model            = Order::class;
    protected static ?string $navigationLabel  = 'Pesanan';
    protected static ?string $modelLabel       = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Pesanan';
    protected static ?int    $navigationSort   = 1;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('invoice_number')->label('Nomor Invoice')->disabled(),
            TextInput::make('customer_name')->label('Nama Customer'),
            TextInput::make('customer_phone')->label('Nomor HP'),
            Select::make('order_status')->label('Status Pesanan')->options(OrderStatus::options()),
            Select::make('payment_status')->label('Status Pembayaran')->options(PaymentStatus::options()),
            TextInput::make('grand_total')->label('Total')->prefix('Rp')->disabled(),
            Textarea::make('notes')->label('Catatan')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('10s')
            ->columns([
                TextColumn::make('invoice_number')->label('Invoice')->searchable()->sortable(),
                TextColumn::make('customer_name')->label('Customer')->searchable(),
                TextColumn::make('table.table_number')->label('Meja')->badge()->color('gray'),
                TextColumn::make('order_status')->label('Status Pesanan')->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),
                TextColumn::make('payment_status')->label('Pembayaran')->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),
                TextColumn::make('grand_total')->label('Total')->money('IDR')->sortable(),
                TextColumn::make('ordered_at')->label('Waktu Order')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('ordered_at', 'desc')
            ->filters([
                SelectFilter::make('order_status')
                    ->label('Status Pesanan')
                    ->options(OrderStatus::options()),

                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options(PaymentStatus::options()),

                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('ordered_at', today()))
                    ->default(),
            ])
            ->actions([
                Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn ($record) => $record->order_status->value === 'pending')
                    ->action(fn ($record) => $record->update(['order_status' => 'confirmed']))
                    ->requiresConfirmation(),

                Action::make('cooking')
                    ->label('Mulai Masak')
                    ->icon('heroicon-o-fire')
                    ->color('warning')
                    ->visible(fn ($record) => $record->order_status->value === 'confirmed')
                    ->action(fn ($record) => $record->update(['order_status' => 'cooking']))
                    ->requiresConfirmation(),

                Action::make('ready')
                    ->label('Siap')
                    ->icon('heroicon-o-bell')
                    ->color('success')
                    ->visible(fn ($record) => $record->order_status->value === 'cooking')
                    ->action(fn ($record) => $record->update(['order_status' => 'ready']))
                    ->requiresConfirmation(),

                Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->visible(fn ($record) => $record->order_status->value === 'ready')
                    ->action(fn ($record) => $record->update([
                        'order_status' => 'completed',
                        'completed_at' => now(),
                    ]))
                    ->requiresConfirmation(),

                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'edit'   => EditOrder::route('/{record}/edit'),
        ];
    }
}