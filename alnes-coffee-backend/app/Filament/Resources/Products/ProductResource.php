<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model            = Product::class;
    protected static ?string $navigationLabel  = 'Produk';
    protected static ?string $modelLabel       = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';
    protected static ?int    $navigationSort   = 3;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-shopping-bag';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Produk')->schema([
                Select::make('category_id')
                    ->label('Kategori')
                    ->options(Category::active()->ordered()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),

                TextInput::make('name')
                    ->label('Nama Menu')
                    ->required()
                    ->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) =>
                        $set('slug', Str::slug($state))
                    ),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3),

                FileUpload::make('image')
                    ->label('Foto Menu')
                    ->image()
                    ->directory('products')
                    ->maxSize(2048),
            ]),

            Section::make('Harga & Stok')->schema([
                TextInput::make('price')
                    ->label('Harga (Rp)')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->minValue(0),

                TextInput::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->default(50)
                    ->minValue(0),

                TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
            ]),

            Section::make('Label & Status')->schema([
                Toggle::make('is_best_seller')->label('Best Seller'),
                Toggle::make('is_popular')->label('Favorit Customer'),
                Toggle::make('is_special')->label('Special Chef'),
                Toggle::make('is_recommended')->label('Rekomendasi'),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Foto')->circular(),
                TextColumn::make('name')->label('Nama Menu')->searchable()->sortable(),
                TextColumn::make('category.name')->label('Kategori')->badge()->color('info'),
                TextColumn::make('price')->label('Harga')->money('IDR')->sortable(),
                TextColumn::make('stock')->label('Stok')->badge()
                    ->color(fn ($state) => $state > 10 ? 'success' : 'danger'),
                IconColumn::make('is_best_seller')->label('Best Seller')->boolean(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')->label('Kategori')
                    ->options(Category::active()->ordered()->pluck('name', 'id')),
                TernaryFilter::make('is_active')->label('Status Aktif'),
                TernaryFilter::make('is_best_seller')->label('Best Seller'),
            ])
            ->actions([EditAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit'   => EditProduct::route('/{record}/edit'),
        ];
    }
}
