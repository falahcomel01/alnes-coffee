<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;  // ← pindah ke sini
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class Settings extends Page
{
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return 'System Management';
    }

    protected static ?string $navigationLabel = 'Pengaturan';
    protected static ?string $title           = 'Pengaturan Café';
    protected static ?int    $navigationSort  = 99;

    protected string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Setting::instance()->toArray());
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Identitas Café')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo Café')
                            ->image()
                            ->directory('settings')
                            ->imageEditor()
                            ->columnSpanFull(),

                        TextInput::make('cafe_name')
                            ->label('Nama Café')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('maps_url')
                            ->label('Google Maps URL')
                            ->url()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Operasional')
                    ->schema([
                        TimePicker::make('open_time')
                            ->label('Jam Buka')
                            ->seconds(false),

                        TimePicker::make('close_time')
                            ->label('Jam Tutup')
                            ->seconds(false),

                        TextInput::make('tax_percentage')
                            ->label('Pajak (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),

                        TextInput::make('service_fee')
                            ->label('Biaya Layanan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(1000),

                        Toggle::make('is_open')
                            ->label('Café Sedang Buka')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Social Media')
                    ->schema([
                        TextInput::make('instagram')
                            ->label('Instagram')
                            ->prefix('@')
                            ->maxLength(255),

                        TextInput::make('facebook')
                            ->label('Facebook')
                            ->maxLength(255),

                        TextInput::make('tiktok')
                            ->label('TikTok')
                            ->prefix('@')
                            ->maxLength(255),
                    ])
                    ->columns(3),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::instance()->update($data);

        Notification::make()
            ->title('Pengaturan berhasil disimpan!')
            ->success()
            ->send();
    }
}