<?php

namespace App\Filament\Resources\CafeTables;

use App\Enums\TableStatus;
use App\Filament\Resources\CafeTables\Pages\CreateCafeTable;
use App\Filament\Resources\CafeTables\Pages\EditCafeTable;
use App\Filament\Resources\CafeTables\Pages\ListCafeTables;
use App\Models\CafeTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class CafeTableResource extends Resource
{
    protected static ?string $model            = CafeTable::class;
    protected static ?string $navigationLabel  = 'Meja';
    protected static ?string $modelLabel       = 'Meja';
    protected static ?string $pluralModelLabel = 'Meja';
    protected static ?int    $navigationSort   = 4;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-square-3-stack-3d';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('table_number')
                ->label('Nomor Meja')
                ->required()
                ->maxLength(20)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $set) =>
                    $set('slug', Str::slug($state))
                ),
            TextInput::make('slug')
                ->label('Slug URL')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),
            Select::make('status')
                ->label('Status')
                ->options(TableStatus::options())
                ->default(TableStatus::Available->value)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('qr_code')
                    ->label('QR Code')
                    ->width(60)
                    ->height(60)
                    ->defaultImageUrl(fn ($record) => null)
                    ->getStateUsing(fn ($record) =>
                        $record->qr_code && Storage::disk('public')->exists($record->qr_code)
                            ? Storage::url($record->qr_code)
                            : null
                    ),
                TextColumn::make('table_number')->label('Nomor Meja')->searchable()->sortable(),
                TextColumn::make('slug')->label('Slug URL'),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color()),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(TableStatus::options()),
            ])
            ->actions([
                Action::make('generate_qr')
                    ->label('Generate QR')
                    ->icon('heroicon-o-qr-code')
                    ->color('success')
                    ->action(function (CafeTable $record) {
                        $url  = config('app.frontend_url', 'http://localhost:5173') . '/table/' . $record->slug;
                        $path = 'qrcodes/table-' . $record->slug . '.svg';

                        $qr = QrCode::format('svg')
                            ->size(300)
                            ->margin(2)
                            ->errorCorrection('H')
                            ->generate($url);

                        Storage::disk('public')->put($path, $qr);

                        $record->update(['qr_code' => $path]);

                        Notification::make()
                            ->success()
                            ->title('QR Code berhasil digenerate!')
                            ->body("QR Code untuk meja {$record->table_number} sudah siap.")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Generate QR Code')
                    ->modalDescription(fn (CafeTable $record) => "Generate QR Code untuk meja {$record->table_number}?")
                    ->modalSubmitActionLabel('Generate'),

                Action::make('download_qr')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (CafeTable $record) =>
                        $record->qr_code && Storage::disk('public')->exists($record->qr_code)
                    )
                    ->url(fn (CafeTable $record) => Storage::url($record->qr_code))
                    ->openUrlInNewTab(),

                EditAction::make(),
                DeleteBulkAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListCafeTables::route('/'),
            'create' => CreateCafeTable::route('/create'),
            'edit'   => EditCafeTable::route('/{record}/edit'),
        ];
    }
}