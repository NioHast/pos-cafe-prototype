<?php

namespace Tests\Unit\Menu;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRecipeModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_recipe_reflects_recipe_existence(): void
    {
        $menu = $this->createMenu('menu-has-recipe');

        $this->assertFalse($menu->hasRecipe());

        $ingredient = Ingredient::create([
            'name' => 'Susu Recipe Test',
            'unit' => 'ml',
            'low_stock_threshold' => 50,
            'is_active' => true,
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $menu->refresh();
        $this->assertTrue($menu->hasRecipe());
    }

    public function test_calculate_cost_uses_average_cost_of_available_batches(): void
    {
        $menu = $this->createMenu('menu-calculate-cost');

        $ingredient = Ingredient::create([
            'name' => 'Kopi Recipe Cost',
            'unit' => 'gram',
            'low_stock_threshold' => 20,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 10,
            'expiry_date' => now()->addDays(15)->toDateString(),
            'received_at' => now()->subDays(2),
            'cost_per_unit' => 2,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 20,
            'expiry_date' => now()->addDays(20)->toDateString(),
            'received_at' => now()->subDay(),
            'cost_per_unit' => 4,
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 5,
        ]);

        $menu->load('menuIngredients.ingredient');

        $this->assertSame(15.0, $menu->calculateCost());
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
            'price' => 15000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);
    }
}