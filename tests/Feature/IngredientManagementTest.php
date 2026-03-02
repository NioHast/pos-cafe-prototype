<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Services\InventoryService;
use App\Services\OrderCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Category $category;
    protected Ingredient $kopi;
    protected Ingredient $susu;
    protected Ingredient $gula;
    protected Menu $kopiSusu;
    protected Menu $menuTanpaResep;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles & Users
        $cashierRole = Role::create(['name' => 'cashier']);
        $this->cashier = User::factory()->create([
            'name' => 'Kasir Test',
            'role_id' => $cashierRole->id,
        ]);

        // Category
        $this->category = Category::create(['name' => 'Minuman', 'is_active' => true]);

        // Ingredients
        $this->kopi = Ingredient::create([
            'name' => 'Kopi Bubuk',
            'unit' => 'gram',
            'low_stock_threshold' => 500,
            'is_active' => true,
        ]);

        $this->susu = Ingredient::create([
            'name' => 'Susu',
            'unit' => 'ml',
            'low_stock_threshold' => 1000,
            'is_active' => true,
        ]);

        $this->gula = Ingredient::create([
            'name' => 'Gula',
            'unit' => 'gram',
            'low_stock_threshold' => 500,
            'is_active' => true,
        ]);

        // Batches (FEFO: kopi has 2 batches with different expiry)
        IngredientBatch::create([
            'ingredient_id' => $this->kopi->id,
            'quantity' => 200,       // expires sooner → should be used first
            'expiry_date' => now()->addMonth(),
            'received_at' => now()->subDays(10),
            'cost_per_unit' => 48,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $this->kopi->id,
            'quantity' => 800,       // expires later
            'expiry_date' => now()->addMonths(6),
            'received_at' => now()->subDays(5),
            'cost_per_unit' => 50,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $this->susu->id,
            'quantity' => 5000,
            'expiry_date' => now()->addDays(14),
            'received_at' => now()->subDays(7),
            'cost_per_unit' => 15,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $this->gula->id,
            'quantity' => 2000,
            'expiry_date' => now()->addYear(),
            'received_at' => now()->subDays(30),
            'cost_per_unit' => 12,
        ]);

        // Menu WITH recipe: Kopi Susu
        $this->kopiSusu = Menu::create([
            'name' => 'Kopi Susu',
            'price' => 15000,
            'student_price' => 12000,
            'status' => 'available',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        MenuIngredient::create([
            'menu_id' => $this->kopiSusu->id,
            'ingredient_id' => $this->kopi->id,
            'quantity_used' => 15,  // 15g kopi per porsi
        ]);

        MenuIngredient::create([
            'menu_id' => $this->kopiSusu->id,
            'ingredient_id' => $this->susu->id,
            'quantity_used' => 150, // 150ml susu per porsi
        ]);

        MenuIngredient::create([
            'menu_id' => $this->kopiSusu->id,
            'ingredient_id' => $this->gula->id,
            'quantity_used' => 10,  // 10g gula per porsi
        ]);

        // Menu WITHOUT recipe
        $this->menuTanpaResep = Menu::create([
            'name' => 'Roti Bakar',
            'price' => 10000,
            'student_price' => 8000,
            'status' => 'available',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);
    }

    // ──────────────────────────────────────
    // Model & Relationship Tests
    // ──────────────────────────────────────

    public function test_ingredient_has_batches_and_calculates_total_stock(): void
    {
        $this->assertEquals(1000, $this->kopi->getTotalStock()); // 200 + 800
        $this->assertEquals(5000, $this->susu->getTotalStock());
        $this->assertEquals(2000, $this->gula->getTotalStock());
    }

    public function test_menu_has_recipe_returns_true_for_menu_with_ingredients(): void
    {
        $this->assertTrue($this->kopiSusu->hasRecipe());
        $this->assertCount(3, $this->kopiSusu->menuIngredients);
    }

    public function test_menu_has_recipe_returns_false_for_menu_without_ingredients(): void
    {
        $this->assertFalse($this->menuTanpaResep->hasRecipe());
        $this->assertCount(0, $this->menuTanpaResep->menuIngredients);
    }

    public function test_menu_ingredient_belongs_to_correct_ingredient(): void
    {
        $recipeItem = $this->kopiSusu->menuIngredients()->first();

        $this->assertNotNull($recipeItem->ingredient);
        $this->assertEquals('Kopi Bubuk', $recipeItem->ingredient->name);
    }

    public function test_ingredient_knows_which_menus_use_it(): void
    {
        $menus = $this->kopi->menus;
        $this->assertCount(1, $menus);
        $this->assertEquals('Kopi Susu', $menus->first()->name);
    }

    // ──────────────────────────────────────
    // Low Stock Tests
    // ──────────────────────────────────────

    public function test_low_stock_detected_when_stock_below_threshold(): void
    {
        // Kopi: stock=1000, threshold=500 → NOT low stock
        $this->assertFalse($this->kopi->getTotalStock() < $this->kopi->low_stock_threshold);

        // Drain kopi to below threshold
        $this->kopi->batches()->update(['quantity' => 0]);
        IngredientBatch::create([
            'ingredient_id' => $this->kopi->id,
            'quantity' => 100,
            'expiry_date' => now()->addMonth(),
            'received_at' => now(),
            'cost_per_unit' => 50,
        ]);

        // Now stock=100, threshold=500 → IS low stock
        $this->assertTrue($this->kopi->getTotalStock() < $this->kopi->low_stock_threshold);
    }

    // ──────────────────────────────────────
    // Stock Reduction via Order Tests
    // ──────────────────────────────────────

    public function test_stock_reduces_fefo_when_order_with_recipe_created(): void
    {
        $this->actingAs($this->cashier);

        $calcService = app(OrderCalculationService::class);

        // Create order for 2x Kopi Susu
        $order = Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_name' => 'Tamu',
            'customer_type' => 'guest',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'total_price' => 0,
        ]);

        $itemData = $calcService->calculateItem($this->kopiSusu, 2);
        $order->items()->create(array_merge($itemData, [
            'menu_id' => $this->kopiSusu->id,
            'quantity' => 2,
            'handled_by' => $this->cashier->id,
        ]));

        OrderObserver::recalculateTotals($order);
        OrderObserver::reduceStockForOrder($order);

        // Kopi: 2 porsi × 15g = 30g needed
        // FEFO: batch1 (200g, expires sooner) should be reduced first → 200 - 30 = 170
        $batches = $this->kopi->batches()->orderBy('expiry_date')->get();
        $this->assertEquals(170, $batches[0]->quantity);   // first batch reduced
        $this->assertEquals(800, $batches[1]->quantity);    // second batch untouched

        // Susu: 2 porsi × 150ml = 300ml → 5000 - 300 = 4700
        $susuStock = $this->susu->getTotalStock();
        $this->assertEquals(4700, $susuStock);

        // Gula: 2 porsi × 10g = 20g → 2000 - 20 = 1980
        $gulaStock = $this->gula->getTotalStock();
        $this->assertEquals(1980, $gulaStock);
    }

    public function test_stock_not_reduced_for_menu_without_recipe(): void
    {
        $this->actingAs($this->cashier);

        $calcService = app(OrderCalculationService::class);

        // Record stock before
        $kopiBefore = $this->kopi->getTotalStock();
        $susuBefore = $this->susu->getTotalStock();

        // Create order for menu WITHOUT recipe
        $order = Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_name' => 'Tamu',
            'customer_type' => 'guest',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'total_price' => 0,
        ]);

        $itemData = $calcService->calculateItem($this->menuTanpaResep, 5);
        $order->items()->create(array_merge($itemData, [
            'menu_id' => $this->menuTanpaResep->id,
            'quantity' => 5,
            'handled_by' => $this->cashier->id,
        ]));

        OrderObserver::recalculateTotals($order);
        OrderObserver::reduceStockForOrder($order);

        // Stock should be completely unchanged
        $this->assertEquals($kopiBefore, $this->kopi->getTotalStock());
        $this->assertEquals($susuBefore, $this->susu->getTotalStock());
    }

    public function test_fefo_depletes_first_batch_then_uses_second(): void
    {
        $this->actingAs($this->cashier);

        $calcService = app(OrderCalculationService::class);

        // First batch has 200g kopi, need to use more than that
        // Order 15 × Kopi Susu = 15 × 15g = 225g kopi needed
        // FEFO: batch1 (200g) fully depleted → 0, batch2 deducted 25g → 775
        $order = Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_name' => 'Tamu',
            'customer_type' => 'guest',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'total_price' => 0,
        ]);

        $itemData = $calcService->calculateItem($this->kopiSusu, 15);
        $order->items()->create(array_merge($itemData, [
            'menu_id' => $this->kopiSusu->id,
            'quantity' => 15,
            'handled_by' => $this->cashier->id,
        ]));

        OrderObserver::recalculateTotals($order);
        OrderObserver::reduceStockForOrder($order);

        $batches = $this->kopi->batches()->orderBy('expiry_date')->get();
        $this->assertEquals(0, $batches[0]->quantity);     // first batch fully depleted
        $this->assertEquals(775, $batches[1]->quantity);    // second batch partially used
    }

    public function test_insufficient_stock_logs_warning_but_does_not_crash_order(): void
    {
        $this->actingAs($this->cashier);

        $calcService = app(OrderCalculationService::class);

        // Drain all kopi stock to only 10g total
        $this->kopi->batches()->delete();
        IngredientBatch::create([
            'ingredient_id' => $this->kopi->id,
            'quantity' => 10,
            'expiry_date' => now()->addMonth(),
            'received_at' => now(),
            'cost_per_unit' => 50,
        ]);

        // Order 2 × Kopi Susu = 30g kopi needed, only 10g available
        $order = Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_name' => 'Tamu',
            'customer_type' => 'guest',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'total_price' => 0,
        ]);

        $itemData = $calcService->calculateItem($this->kopiSusu, 2);
        $order->items()->create(array_merge($itemData, [
            'menu_id' => $this->kopiSusu->id,
            'quantity' => 2,
            'handled_by' => $this->cashier->id,
        ]));

        OrderObserver::recalculateTotals($order);

        // reduceStockForOrder should NOT throw — it catches and logs
        OrderObserver::reduceStockForOrder($order);

        // Order still exists and is valid
        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        // Stock was NOT reduced (transaction rolled back inside InventoryService)
        $this->assertEquals(10, $this->kopi->getTotalStock());
    }

    // ──────────────────────────────────────
    // InventoryService Direct Tests
    // ──────────────────────────────────────

    public function test_can_fulfill_order_returns_true_when_stock_sufficient(): void
    {
        $service = app(InventoryService::class);

        $result = $service->canFulfillOrder([
            ['menu_id' => $this->kopiSusu->id, 'quantity' => 2],
        ]);

        $this->assertTrue($result['can_fulfill']);
        $this->assertEmpty($result['insufficient_ingredients']);
    }

    public function test_can_fulfill_order_returns_false_when_stock_insufficient(): void
    {
        $service = app(InventoryService::class);

        // Total kopi = 1000g, 1 porsi = 15g, so 100 porsi = 1500g > 1000g
        $result = $service->canFulfillOrder([
            ['menu_id' => $this->kopiSusu->id, 'quantity' => 100],
        ]);

        $this->assertFalse($result['can_fulfill']);
        $this->assertNotEmpty($result['insufficient_ingredients']);
        $this->assertEquals('Kopi Bubuk', $result['insufficient_ingredients'][0]['ingredient_name']);
    }

    public function test_calculate_cost_returns_hpp_for_menu_with_recipe(): void
    {
        // Eager-load to avoid LazyLoadingViolationException
        $menu = Menu::with('menuIngredients.ingredient.batches')->find($this->kopiSusu->id);

        // Kopi Susu recipe:
        // - 15g Kopi × avg(48, 50) = 15 × 49 = 735
        // - 150ml Susu × 15 = 2250
        // - 10g Gula × 12 = 120
        // Total HPP = 735 + 2250 + 120 = 3105
        $cost = $menu->calculateCost();

        $this->assertGreaterThan(0, $cost);
        // Kopi avg cost: (48 + 50) / 2 = 49 → 15 × 49 = 735
        // Susu avg cost: 15 → 150 × 15 = 2250
        // Gula avg cost: 12 → 10 × 12 = 120
        // Total = 3105
        $this->assertEquals(3105, $cost);
    }

    // ──────────────────────────────────────
    // Soft Delete Tests
    // ──────────────────────────────────────

    public function test_soft_deleted_ingredient_preserves_recipe_data(): void
    {
        // Soft delete the ingredient
        $this->kopi->delete();

        // Ingredient is soft deleted
        $this->assertSoftDeleted('ingredients', ['id' => $this->kopi->id]);

        // But recipe data (menu_ingredients) still exists
        $this->assertDatabaseHas('menu_ingredients', [
            'ingredient_id' => $this->kopi->id,
            'menu_id' => $this->kopiSusu->id,
        ]);

        // Menu still reports having a recipe
        $this->assertTrue($this->kopiSusu->hasRecipe());
    }

    public function test_menu_delete_cascades_to_recipe(): void
    {
        $menuId = $this->kopiSusu->id;

        // Verify recipes exist
        $this->assertEquals(3, MenuIngredient::where('menu_id', $menuId)->count());

        // Force delete the menu (cascade should remove menu_ingredients)
        $this->kopiSusu->forceDelete();

        // All recipes for this menu should be gone
        $this->assertEquals(0, MenuIngredient::where('menu_id', $menuId)->count());
    }
}
