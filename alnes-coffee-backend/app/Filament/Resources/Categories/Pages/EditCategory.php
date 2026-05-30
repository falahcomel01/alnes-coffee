<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function ($record) {
                    // Hapus produk soft-deleted dulu
                    \App\Models\Product::withTrashed()
                        ->where('category_id', $record->id)
                        ->whereNotNull('deleted_at')
                        ->forceDelete();

                    // Cek apakah masih ada produk aktif
                    if ($record->products()->exists()) {
                        Notification::make()
                            ->title('Tidak bisa dihapus!')
                            ->body('Kategori "' . $record->name . '" masih memiliki ' . $record->products()->count() . ' produk aktif. Hapus atau pindahkan produk terlebih dahulu.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->delete();

                    Notification::make()
                        ->title('Kategori berhasil dihapus!')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }
}