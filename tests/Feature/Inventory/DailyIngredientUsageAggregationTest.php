<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\DailyIngredientUsage;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyIngredientUsageAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_cash_aggregates_usage_without_creating_duplicate_rows(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $menu = $this->createMenu();
        $ingredient = $this->createIngredientWithBatch(200);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 15,
        ]);

        $orderOne = $this->createPendingCashOrder($menu->id, 1);
        $orderTwo = $this->createPendingCashOrder($menu->id, 2);

        $this->actingAs($cashier)
            ->patchJson("/cashier/order/{$orderOne->id}/confirm-cash")
            ->assertOk();

        $this->actingAs($cashier)
            ->patchJson("/cashier/order/{$orderTwo->id}/confirm-cash")
            ->assertOk();

        $dailyUsageRows = DailyIngredientUsage::query()
            ->where('ingredient_id', $ingredient->id)
            ->whereDate('usage_date', now()->toDateString())
            ->get();

        $this->assertCount(1, $dailyUsageRows);
        $this->assertSame(45.0, (float) $dailyUsageRows->first()->jumlah_digunakan);
    }

    public function test_processing_same_order_sale_twice_is_idempotent(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $menu = $this->createMenu();
        $ingredient = $this->createIngredientWithBatch(100);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $order = $this->createPendingCashOrder($menu->id, 2);

        $service = app(InventoryService::class);

        $firstRun = $service->processSaleForOrder($order, $cashier->id);
        $secondRun = $service->processSaleForOrder($order, $cashier->id);

        $this->assertTrue($firstRun['success']);
        $this->assertTrue($secondRun['success']);
        $this->assertTrue((bool) ($secondRun['skipped'] ?? false));

        $this->assertDatabaseCount('daily_ingredient_usages', 1);

        $dailyUsage = DailyIngredientUsage::firstOrFail();

        $this->assertSame(20.0, (float) $dailyUsage->jumlah_digunakan);

        $this->assertDatabaseCount('stock_movements', 1);
        $this->assertDatabaseHas('stock_movements', [
            'order_id' => $order->id,
            'movement_type' => 'sale',
        ]);
    }

    public function test_cashier_order_store_immediately_records_daily_usage(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $menu = $this->createMenu();
        $ingredient = $this->createIngredientWithBatch(120);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 12,
        ]);

        $this->actingAs($cashier)
            ->post('/cashier/pesanan-baru', [
                'payment_method' => 'cash',
                'customer_name' => 'Walk In Test',
                'items' => [
                    ['menu_id' => $menu->id, 'quantity' => 3],
                ],
            ])
            ->assertRedirect();

        $order = Order::latest('id')->firstOrFail();

        $this->assertSame(Order::STATUS_DIPROSES, $order->status);

        $dailyUsage = DailyIngredientUsage::query()
            ->where('ingredient_id', $ingredient->id)
            ->whereDate('usage_date', now()->toDateString())
            ->first();

        $this->assertNotNull($dailyUsage);
        $this->assertSame(36.0, (float) $dailyUsage->jumlah_digunakan);
    }

    private function createMenu(): Menu
    {
        $category = Category::create([
            'name' => 'Kategori Mining',
            'slug' => 'kategori-mining',
            'is_active' => true,
        ]);

        return Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu Mining',
            'slug' => 'menu-mining-' . uniqid(),
            'description' => null,
            'price' => 10000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);
    }

    private function createIngredientWithBatch(float $quantity): Ingredient
    {
        $ingredient = Ingredient::create([
            'name' => 'Bahan Mining',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => $quantity,
            'expiry_date' => now()->addDays(10)->toDateString(),
            'received_at' => now(),
            'cost_per_unit' => 1,
        ]);

        return $ingredient;
    }

    private function createPendingCashOrder(int $menuId, int $quantity): Order
    {
        $order = Order::create([
            'customer_name' => 'Customer Mining',
            'customer_phone' => '081234567890',
            'order_type' => 'qr',
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash',
            'is_paid' => false,
            'total_amount' => 10000 * $quantity,
        ]);

        $order->items()->create([
            'menu_id' => $menuId,
            'quantity' => $quantity,
            'unit_price' => 10000,
            'subtotal' => 10000 * $quantity,
        ]);

        return $order->fresh();
    }
}
