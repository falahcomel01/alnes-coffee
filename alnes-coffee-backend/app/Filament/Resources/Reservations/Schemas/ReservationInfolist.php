<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReservationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Informasi Reservasi')
                ->icon('heroicon-o-calendar-days')
                ->columns(2)
                ->schema([
                    TextEntry::make('table.table_number')
                        ->label('Meja')
                        ->badge()
                        ->color('gray')
                        ->placeholder('Belum ditentukan'),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn ($state) => match ($state?->value ?? $state) {
                            'pending'   => 'warning',
                            'confirmed' => 'success',
                            'cancelled' => 'danger',
                            'completed' => 'gray',
                            default     => 'gray',
                        })
                        ->formatStateUsing(fn ($state) => match ($state?->value ?? $state) {
                            'pending'   => '⏳ Pending',
                            'confirmed' => '✅ Confirmed',
                            'cancelled' => '❌ Cancelled',
                            'completed' => '🏁 Completed',
                            default     => $state,
                        }),

                    TextEntry::make('reservation_date')
                        ->label('Tanggal Reservasi')
                        ->date('d M Y'),

                    TextEntry::make('reservation_time')
                        ->label('Jam Reservasi')
                        ->time('H:i'),

                    TextEntry::make('guest_count')
                        ->label('Jumlah Tamu')
                        ->formatStateUsing(fn ($state) => $state . ' orang'),

                    TextEntry::make('created_at')
                        ->label('Dibuat Pada')
                        ->dateTime('d M Y H:i'),
                ]),

            Section::make('Data Customer')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextEntry::make('customer_name')
                        ->label('Nama Customer'),

                    TextEntry::make('customer_phone')
                        ->label('Nomor HP'),

                    TextEntry::make('customer_email')
                        ->label('Email')
                        ->placeholder('-'),

                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

            Section::make('Riwayat Status')
                ->icon('heroicon-o-clock')
                ->columns(2)
                ->schema([
                    TextEntry::make('confirmed_at')
                        ->label('Dikonfirmasi Pada')
                        ->dateTime('d M Y H:i')
                        ->placeholder('-'),

                    TextEntry::make('cancelled_at')
                        ->label('Dibatalkan Pada')
                        ->dateTime('d M Y H:i')
                        ->placeholder('-'),

                    TextEntry::make('cancellation_reason')
                        ->label('Alasan Pembatalan')
                        ->placeholder('-')
                        ->columnSpanFull(),
                ]),

        ]);
    }
}