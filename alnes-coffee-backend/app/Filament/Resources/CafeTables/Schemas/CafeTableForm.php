<?php

namespace App\Filament\Resources\CafeTables\Schemas;

use App\Enums\TableStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CafeTableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('table_number')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('qr_code')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(TableStatus::class)
                    ->default('available')
                    ->required(),
            ]);
    }
}
