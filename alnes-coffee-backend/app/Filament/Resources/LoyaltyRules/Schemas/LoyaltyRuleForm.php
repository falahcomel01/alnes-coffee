<?php

namespace App\Filament\Resources\LoyaltyRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LoyaltyRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('branch_id')
                    ->numeric(),
                TextInput::make('created_by')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options([
            'transaction' => 'Transaction',
            'product' => 'Product',
            'tier_bonus' => 'Tier bonus',
            'birthday' => 'Birthday',
        ])
                    ->default('transaction')
                    ->required(),
                TextInput::make('earn_per_amount')
                    ->required()
                    ->numeric()
                    ->default(1000.0),
                TextInput::make('minimum_transaction')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('multiplier')
                    ->required()
                    ->numeric()
                    ->default(1.0),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
