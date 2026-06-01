<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('points_balance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_points_earned')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('tier')
                    ->options(['bronze' => 'Bronze', 'silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum'])
                    ->default('bronze')
                    ->required(),
                DateTimePicker::make('tier_updated_at'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
