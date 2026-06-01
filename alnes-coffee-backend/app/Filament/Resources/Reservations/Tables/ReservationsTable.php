<?php

namespace App\Filament\Resources\Reservations\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reservation_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('reservation_time')
                    ->label('Jam')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer_phone')
                    ->label('Nomor HP')
                    ->searchable(),

                TextColumn::make('table.table_number')
                    ->label('Meja')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Belum ditentukan'),

                TextColumn::make('guest_count')
                    ->label('Tamu')
                    ->numeric()
                    ->alignCenter(),

                TextColumn::make('status')
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

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('reservation_date', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'   => '⏳ Pending',
                        'confirmed' => '✅ Confirmed',
                        'cancelled' => '❌ Cancelled',
                        'completed' => '🏁 Completed',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => ($record->status?->value ?? $record->status) === 'pending')
                    ->action(fn ($record) => $record->update([
                        'status'       => 'confirmed',
                        'confirmed_at' => now(),
                    ]))
                    ->requiresConfirmation(),

                Action::make('complete')
                    ->label('Selesai')
                    ->icon('heroicon-o-flag')
                    ->color('gray')
                    ->visible(fn ($record) => ($record->status?->value ?? $record->status) === 'confirmed')
                    ->action(fn ($record) => $record->update(['status' => 'completed']))
                    ->requiresConfirmation(),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => in_array(
                        $record->status?->value ?? $record->status,
                        ['pending', 'confirmed']
                    ))
                    ->action(fn ($record) => $record->update([
                        'status'       => 'cancelled',
                        'cancelled_at' => now(),
                    ]))
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}