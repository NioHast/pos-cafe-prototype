<?php

namespace Tests\Feature\Menu;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRecipeParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_defaults_to_not_stock_calculated_when_created_without_recipe(): void
    {
        $menu = $this->createMenu('menu-default-flag');

        $this->assertFalse((bool) $menu->is_stock_calculated);
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'is_stock_calculated' => false,
        ]);
    }

    public function test_stock_flag_becomes_true_when_recipe_is_added(): void
    {
        $menu = $this->createMenu('menu-flag-true');
        $ingredient = $this->createIngredient('Bahan Flag True', 'gram');

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 12,
        ]);

        $menu->refresh();

        $this->assertTrue((bool) $menu->is_stock_calculated);
    }

    public function test_stock_flag_returns_false_when_last_recipe_item_is_deleted(): void
    {
        $menu = $this->createMenu('menu-flag-reset');
        $ingredient = $this->createIngredient('Bahan Flag Reset', 'ml');

        $recipe = MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 25,
        ]);

        $recipe->delete();
        $menu->refresh();

        $this->assertFalse((bool) $menu->is_stock_calculated);
    }

    public function test_recipe_changes_do_not_modify_menu_prices(): void
    {
        $menu = $this->createMenu('menu-price-stable');
        $menu->update([
            'price' => 18000,
            'is_student_discount' => true,
            'student_price' => 15000,
        ]);

        $ingredient = $this->createIngredient('Bahan Price Stable', 'gram');

        $recipe = MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 8,
        ]);

        $recipe->delete();

        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'price' => 18000,
            'is_student_discount' => true,
            'student_price' => 15000,
        ]);
    }

    private function createMenu(string $slug): Menu
    {
        $category = Category::create([
            'name' => 'Kategori ' . $slug,
            'slug' => 'kategori-' . $slug,
            'is_active' => true,
        ]);

        return Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu ' . $slug,
            'slug' => $slug,
            'description' => null,
            'price' => 10000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);
    }

    private function createIngredient(string $name, string $unit): Ingredient
    {
        return Ingredient::create([
            'name' => $name,
            'unit' => $unit,
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);
    }
}