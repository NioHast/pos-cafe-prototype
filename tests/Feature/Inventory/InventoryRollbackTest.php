<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Services\InventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryRollbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_deduction_rolls_back_when_stock_is_insufficient(): void
    {
        $category = Category::create([
            'name' => 'Minuman Rollback',
            'slug' => 'minuman-rollback',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu Rollback',
            'slug' => 'menu-rollback',
            'description' => null,
            'price' => 10000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Gula Rollback',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);

        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 20,
            'expiry_date' => now()->addDays(10)->toDateString(),
            'received_at' => now(),
            'cost_per_unit' => 1,
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 15,
        ]);

        $service = app(InventoryService::class);

        try {
            $service->decreaseStockForOrder([
                ['menu_id' => $menu->id, 'quantity' => 2],
            ]);

            $this->fail('Expected insufficient stock exception was not thrown.');
        } catch (Exception $exception) {
            $this->assertStringContainsString('Stok tidak mencukupi', $exception->getMessage());
        }

        $batch->refresh();

        $this->assertSame(20.0, (float) $batch->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
