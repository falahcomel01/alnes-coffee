<?php

namespace App\Filament\Resources\LoyaltyRules;

use App\Filament\Resources\LoyaltyRules\Pages\CreateLoyaltyRule;
use App\Filament\Resources\LoyaltyRules\Pages\EditLoyaltyRule;
use App\Filament\Resources\LoyaltyRules\Pages\ListLoyaltyRules;
use App\Models\LoyaltyRule;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\{Hidden, Select, TextInput, Toggle};
use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LoyaltyRuleResource extends Resource
{
    protected static ?string $model            = LoyaltyRule::class;
    protected static ?string $navigationLabel  = 'Loyalty Rules';
    protected static ?string $modelLabel       = 'Loyalty Rule';
    protected static ?string $pluralModelLabel = 'Loyalty Rules';
    protected static ?int    $navigationSort   = 2;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): string|\BackedEnum|null
    {
        return 'Loyalty System';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Hidden::make('created_by')
                ->default(fn() => auth()->id()),

            TextInput::make('name')
                ->label('Nama Rule')
                ->required()
                ->maxLength(150),

            Select::make('type')
                ->label('Tipe Rule')
                ->options([
                    'transaction' => 'Per Transaksi',
                    'tier_bonus'  => 'Bonus Tier',
                    'birthday'    => 'Birthday Bonus',
                ])
                ->required()
                ->default('transaction'),

            TextInput::make('earn_per_amount')
                ->label('Rp per 1 Poin')
                ->numeric()
                ->required()
                ->default(1000)
                ->prefix('Rp')
                ->helperText('Contoh: 1000 = customer dapat 1 poin setiap Rp 1.000 transaksi'),

            TextInput::make('minimum_transaction')
                ->label('Minimum Transaksi')
                ->numeric()
                ->required()
                ->default(0)
                ->prefix('Rp'),

            TextInput::make('multiplier')
                ->label('Multiplier Poin')
                ->numeric()
                ->required()
                ->default(1.00)
                ->step(0.25)
                ->helperText('1.00 = normal, 1.5 = bonus 50%, 2.00 = double poin'),

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
                    ->label('Nama Rule')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'transaction' => 'primary',
                        'tier_bonus'  => 'success',
                        'birthday'    => 'warning',
                        default       => 'gray',
                    }),

                TextColumn::make('earn_per_amount')
                    ->label('Rp / Poin')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('minimum_transaction')
                    ->label('Min. Transaksi')
                    ->money('IDR'),

                TextColumn::make('multiplier')
                    ->label('Multiplier')
                    ->formatStateUsing(fn($state) => $state . 'x')
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListLoyaltyRules::route('/'),
            'create' => CreateLoyaltyRule::route('/create'),
            'edit'   => EditLoyaltyRule::route('/{record}/edit'),
        ];
    }
}