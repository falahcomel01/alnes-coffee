<?php

namespace App\Filament\Resources\Reservations\Schemas;

use App\Models\CafeTable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('table_id')
                ->label('Meja')
                ->options(CafeTable::pluck('table_number', 'id'))
                ->searchable()
                ->nullable()
                ->placeholder('Pilih meja (opsional)'),

            TextInput::make('customer_name')
                ->label('Nama Customer')
                ->required()
                ->maxLength(100),

            TextInput::make('customer_phone')
                ->label('Nomor HP')
                ->tel()
                ->required()
                ->maxLength(20),

            TextInput::make('customer_email')
                ->label('Email')
                ->email()
                ->nullable(),

            DatePicker::make('reservation_date')
                ->label('Tanggal Reservasi')
                ->required()
                ->minDate(today()),

            TimePicker::make('reservation_time')
                ->label('Jam Reservasi')
                ->required()
                ->seconds(false),

            TextInput::make('guest_count')
                ->label('Jumlah Tamu')
                ->required()
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->maxValue(20),

            Select::make('status')
                ->label('Status')
                ->options([
                    'pending'   => 'Pending',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                    'completed' => 'Completed',
                ])
                ->default('pending')
                ->required(),

            Textarea::make('notes')
                ->label('Catatan')
                ->rows(3)
                ->columnSpanFull(),

            TextInput::make('cancellation_reason')
                ->label('Alasan Pembatalan')
                ->nullable()
                ->columnSpanFull(),
        ]);
    }
}