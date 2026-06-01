<?php

namespace App\Filament\Resources\LoyaltyRewards\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LoyaltyRewardForm
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
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->image(),
                Select::make('type')
                    ->options([
            'discount' => 'Discount',
            'free_item' => 'Free item',
            'cashback' => 'Cashback',
            'voucher' => 'Voucher',
        ])
                    ->required(),
                TextInput::make('points_required')
                    ->required()
                    ->numeric(),
                TextInput::make('value')
                    ->required()
                    ->numeric(),
                TextInput::make('stock')
                    ->numeric(),
                Select::make('min_tier')
                    ->options(['bronze' => 'Bronze', 'silver' => 'Silver', 'gold' => 'Gold', 'platinum' => 'Platinum']),
                DateTimePicker::make('expired_at'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
