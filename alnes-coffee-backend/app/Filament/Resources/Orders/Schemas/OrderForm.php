<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->required(),
                Select::make('table_id')
                    ->relationship('table', 'id'),
                Select::make('promo_id')
                    ->relationship('promo', 'title'),
                TextInput::make('customer_name'),
                TextInput::make('customer_phone')
                    ->tel(),
                Select::make('order_type')
                    ->options(OrderType::class)
                    ->default('dine_in')
                    ->required(),
                Select::make('payment_method')
                    ->options(PaymentMethod::class),
                Select::make('payment_status')
                    ->options(PaymentStatus::class)
                    ->default('unpaid')
                    ->required(),
                Select::make('order_status')
                    ->options([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'cooking' => 'Cooking',
            'ready' => 'Ready',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('service_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('grand_total')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('notes')
                    ->columnSpanFull(),
                DateTimePicker::make('ordered_at'),
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
