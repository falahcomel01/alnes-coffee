<?php

namespace App\Filament\Resources\Promos;

use App\Filament\Resources\Promos\Pages\CreatePromo;
use App\Filament\Resources\Promos\Pages\EditPromo;
use App\Filament\Resources\Promos\Pages\ListPromos;
use App\Models\Promo;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PromoResource extends Resource
{
    protected static ?string $model            = Promo::class;
    protected static ?string $navigationLabel  = 'Promo';
    protected static ?string $modelLabel       = 'Promo';
    protected static ?string $pluralModelLabel = 'Promo';
    protected static ?int    $navigationSort   = 5;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-ticket';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')->label('Kode Voucher')->required()->maxLength(50)->unique(ignoreRecord: true),
            TextInput::make('title')->label('Nama Promo')->required()->maxLength(150),
            Select::make('type')->label('Tipe Diskon')->options(['percentage' => 'Persentase (%)', 'fixed' => 'Nominal (Rp)'])->required(),
            TextInput::make('value')->label('Nilai Diskon')->numeric()->required()->minValue(0),
            TextInput::make('minimum_purchase')->label('Minimal Transaksi (Rp)')->numeric()->default(0)->prefix('Rp'),
            TextInput::make('maximum_discount')->label('Maksimal Diskon (Rp)')->numeric()->prefix('Rp')->nullable(),
            TextInput::make('usage_limit')->label('Batas Penggunaan')->numeric()->nullable()->helperText('Kosongkan untuk unlimited'),
            DateTimePicker::make('expired_at')->label('Berlaku Sampai')->nullable(),
            Toggle::make('is_active')->label('Aktif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->badge()->color('warning'),
                TextColumn::make('title')->label('Nama Promo')->searchable(),
                TextColumn::make('type')->label('Tipe')->badge()
                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'Persentase' : 'Nominal')
                    ->color(fn ($state) => $state === 'percentage' ? 'info' : 'success'),
                TextColumn::make('value')->label('Nilai')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->type === 'percentage' ? $state . '%' : 'Rp' . number_format($state, 0, ',', '.')
                    ),
                TextColumn::make('used_count')->label('Digunakan')->badge()->color('gray'),
                TextColumn::make('expired_at')->label('Expired')->dateTime('d M Y')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')->label('Status Aktif')])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListPromos::route('/'),
            'create' => CreatePromo::route('/create'),
            'edit'   => EditPromo::route('/{record}/edit'),
        ];
    }
}
