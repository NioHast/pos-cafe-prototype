<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use App\Models\MenuIngredient;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected static bool $canCreateAnother = false;

    /**
     * Mutate form data before create: strip recipe_items (not a DB column).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['recipe_items']);

        return $data;
    }

    /**
     * After the menu record is created, persist recipe items.
     */
    protected function afterCreate(): void
    {
        $recipeItems = $this->data['recipe_items'] ?? [];

        foreach ($recipeItems as $item) {
            if (!empty($item['ingredient_id']) && !empty($item['quantity_used'])) {
                MenuIngredient::create([
                    'menu_id' => $this->record->id,
                    'ingredient_id' => $item['ingredient_id'],
                    'quantity_used' => $item['quantity_used'],
                ]);
            }
        }

        if (empty($recipeItems)) {
            Notification::make()
                ->warning()
                ->title('Menu dibuat tanpa resep')
                ->body('Stok bahan tidak akan dikurangi saat menu ini dipesan. Anda bisa menambahkan resep nanti di halaman edit.')
                ->persistent()
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('Menu berhasil dibuat')
                ->body('Menu beserta ' . count($recipeItems) . ' bahan resep berhasil disimpan.')
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
