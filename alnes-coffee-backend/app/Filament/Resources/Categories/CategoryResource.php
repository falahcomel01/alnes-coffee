<?php

namespace App\Filament\Resources\Categories;

use App\Enums\CategoryType;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model            = Category::class;
    protected static ?string $navigationLabel  = 'Kategori';
    protected static ?string $modelLabel       = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategori';
    protected static ?int    $navigationSort   = 2;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-tag';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Kategori')
                ->required()
                ->maxLength(100)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $set) =>
                    $set('slug', Str::slug($state))
                ),

            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->maxLength(120)
                ->unique(ignoreRecord: true),

            TextInput::make('icon')
                ->label('Icon (emoji)')
                ->maxLength(100)
                ->placeholder('contoh: ☕ atau 🍔'),

            Select::make('type')
                ->label('Tipe')
                ->options(CategoryType::options())
                ->required(),

            TextInput::make('sort_order')
                ->label('Urutan Tampil')
                ->numeric()
                ->default(0),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable()->width(50),
                TextColumn::make('icon')->label('Icon'),
                TextColumn::make('name')->label('Nama Kategori')->searchable()->sortable(),
                TextColumn::make('type')->label('Tipe')->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => match($state) {
                        CategoryType::Food      => 'warning',
                        CategoryType::Beverages => 'info',
                        default                 => 'gray',
                    }),
                TextColumn::make('products_count')->label('Produk')->counts('products')->badge()->color('success'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('type')->label('Tipe')->options(CategoryType::options()),
                TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit'   => EditCategory::route('/{record}/edit'),
        ];
    }
}
