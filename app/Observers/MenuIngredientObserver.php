<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\MenuIngredient;

class MenuIngredientObserver
{
    public function created(MenuIngredient $menuIngredient): void
    {
        $this->refreshMenuStockFlag((int) $menuIngredient->menu_id);
    }

    public function updated(MenuIngredient $menuIngredient): void
    {
        if ($menuIngredient->wasChanged('menu_id')) {
            $this->refreshMenuStockFlag((int) $menuIngredient->getOriginal('menu_id'));
        }

        $this->refreshMenuStockFlag((int) $menuIngredient->menu_id);
    }

    public function deleted(MenuIngredient $menuIngredient): void
    {
        $this->refreshMenuStockFlag((int) $menuIngredient->menu_id);
    }

    private function refreshMenuStockFlag(int $menuId): void
    {
        if (! $menuId) {
            return;
        }

        $menu = Menu::find($menuId);

        if (! $menu) {
            return;
        }

        $menu->refreshStockCalculatedFlag();
    }
}