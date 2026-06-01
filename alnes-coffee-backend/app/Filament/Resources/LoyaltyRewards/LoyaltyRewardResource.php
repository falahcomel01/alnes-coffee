<?php

namespace App\Filament\Resources\LoyaltyRewards;

use App\Filament\Resources\LoyaltyRewards\Pages\CreateLoyaltyReward;
use App\Filament\Resources\LoyaltyRewards\Pages\EditLoyaltyReward;
use App\Filament\Resources\LoyaltyRewards\Pages\ListLoyaltyRewards;
use App\Models\LoyaltyReward;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\{DateTimePicker, FileUpload, Hidden, Select, Textarea, TextInput, Toggle};
use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\{IconColumn, ImageColumn, TextColumn};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Table;

class LoyaltyRewardResource extends Resource
{
    protected static ?string $model            = LoyaltyReward::class;
    protected static ?string $navigationLabel  = 'Rewards';
    protected static ?string $modelLabel       = 'Reward';
    protected static ?string $pluralModelLabel = 'Rewards';
    protected static ?int    $navigationSort   = 3;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-gift';
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
                ->label('Nama Reward')
                ->required()
                ->maxLength(150),

            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->nullable(),

            FileUpload::make('image')
                ->label('Gambar Reward')
                ->image()
                ->directory('rewards')
                ->nullable(),

            Select::make('type')
                ->label('Tipe Reward')
                ->options([
                    'discount'  => 'Diskon',
                    'free_item' => 'Item Gratis',
                    'cashback'  => 'Cashback',
                    'voucher'   => 'Voucher',
                ])
                ->required(),

            TextInput::make('points_required')
                ->label('Poin yang Dibutuhkan')
                ->numeric()
                ->required()
                ->suffix('poin'),

            TextInput::make('value')
                ->label('Nilai Reward')
                ->numeric()
                ->required()
                ->prefix('Rp')
                ->helperText('Untuk diskon/cashback: nominal. Untuk free_item: harga item.'),

            TextInput::make('stock')
                ->label('Stok')
                ->numeric()
                ->nullable()
                ->helperText('Kosongkan untuk unlimited'),

            Select::make('min_tier')
                ->label('Minimum Tier')
                ->options([
                    'bronze'   => '🥉 Bronze',
                    'silver'   => '🥈 Silver',
                    'gold'     => '🥇 Gold',
                    'platinum' => '💎 Platinum',
                ])
                ->nullable()
                ->helperText('Kosongkan jika berlaku untuk semua tier'),

            DateTimePicker::make('expired_at')
                ->label('Tanggal Expired')
                ->nullable(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nama Reward')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'discount'  => 'danger',
                        'free_item' => 'success',
                        'cashback'  => 'warning',
                        'voucher'   => 'primary',
                        default     => 'gray',
                    }),

                TextColumn::make('points_required')
                    ->label('Poin')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->suffix(' poin'),

                TextColumn::make('value')
                    ->label('Nilai')
                    ->money('IDR'),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->formatStateUsing(fn($state) => $state ?? '∞')
                    ->alignCenter(),

                TextColumn::make('min_tier')
                    ->label('Min. Tier')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'bronze'   => 'warning',
                        'silver'   => 'gray',
                        'gold'     => 'success',
                        'platinum' => 'primary',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn($state) => $state ? ucfirst($state) : 'Semua'),

                TextColumn::make('expired_at')
                    ->label('Expired')
                    ->date('d M Y'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'discount'  => 'Diskon',
                        'free_item' => 'Item Gratis',
                        'cashback'  => 'Cashback',
                        'voucher'   => 'Voucher',
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
            'index'  => ListLoyaltyRewards::route('/'),
            'create' => CreateLoyaltyReward::route('/create'),
            'edit'   => EditLoyaltyReward::route('/{record}/edit'),
        ];
    }
}