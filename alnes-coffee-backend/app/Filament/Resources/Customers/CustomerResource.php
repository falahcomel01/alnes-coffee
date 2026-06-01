<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\{DateTimePicker, Select, TextInput, Toggle};
use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model            = Customer::class;
    protected static ?string $navigationLabel  = 'Customers';
    protected static ?string $modelLabel       = 'Customer';
    protected static ?string $pluralModelLabel = 'Customers';
    protected static ?int    $navigationSort   = 1;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): string|\BackedEnum|null
    {
        return 'Loyalty System';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(100),

            TextInput::make('phone')
                ->label('Nomor HP')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->nullable(),

            Select::make('tier')
                ->label('Tier')
                ->options([
                    'bronze'   => '🥉 Bronze',
                    'silver'   => '🥈 Silver',
                    'gold'     => '🥇 Gold',
                    'platinum' => '💎 Platinum',
                ])
                ->required()
                ->default('bronze'),

            TextInput::make('points_balance')
                ->label('Saldo Poin')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(false),

            TextInput::make('total_points_earned')
                ->label('Total Poin Sepanjang Waktu')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(false),

            DateTimePicker::make('tier_updated_at')
                ->label('Tier Diperbarui')
                ->disabled()
                ->dehydrated(false),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Nomor HP')
                    ->searchable(),

                TextColumn::make('tier')
                    ->label('Tier')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'bronze'   => 'warning',
                        'silver'   => 'gray',
                        'gold'     => 'success',
                        'platinum' => 'primary',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'bronze'   => '🥉 Bronze',
                        'silver'   => '🥈 Silver',
                        'gold'     => '🥇 Gold',
                        'platinum' => '💎 Platinum',
                        default    => $state,
                    }),

                TextColumn::make('points_balance')
                    ->label('Saldo Poin')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('total_points_earned')
                    ->label('Total Poin')
                    ->numeric()
                    ->sortable()
                    ->alignRight(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tier')
                    ->options([
                        'bronze'   => '🥉 Bronze',
                        'silver'   => '🥈 Silver',
                        'gold'     => '🥇 Gold',
                        'platinum' => '💎 Platinum',
                    ]),
                TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit'   => EditCustomer::route('/{record}/edit'),
        ];
    }
}