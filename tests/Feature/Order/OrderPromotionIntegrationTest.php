<?php

namespace Tests\Feature\Order;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Order;
use App\Models\Menu;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPromotionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_checkout_applies_best_available_promotion(): void
    {
        $category = Category::create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Americano',
            'slug' => 'americano',
            'description' => null,
            'price' => 10000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $table = CafeTable::create([
            'table_number' => 1,
            'qr_code' => 'table-1',
            'is_available' => true,
        ]);

        $categoryPromotion = Promotion::create([
            'name' => 'Category 10%',
            'type' => Promotion::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'min_purchase' => null,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        $categoryPromotion->rules()->create([
            'applicable_type' => 'category',
            'applicable_id' => $category->id,
        ]);

        $menuPromotion = Promotion::create([
            'name' => 'Menu 3000',
            'type' => Promotion::TYPE_FIXED_AMOUNT,
            'discount_value' => 3000,
            'min_purchase' => null,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        $menuPromotion->rules()->create([
            'applicable_type' => 'menu',
            'applicable_id' => $menu->id,
        ]);

        $response = $this->postJson('/api/order', [
            'customer_name' => 'Customer QR',
            'customer_phone' => '081234567890',
            'table_id' => $table->id,
            'is_mahasiswa' => false,
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated();

        $order = Order::with('appliedPromotions')
            ->findOrFail((int) $response->json('order_id'));

        $this->assertSame(17000.0, (float) $order->total_amount);
        $this->assertCount(1, $order->appliedPromotions);

        $appliedPromotion = $order->appliedPromotions->first();

        $this->assertSame($menuPromotion->id, $appliedPromotion->promotion_id);
        $this->assertSame(3000.0, (float) $appliedPromotion->discount_amount);

        $menuPromotion->refresh();
        $categoryPromotion->refresh();

        $this->assertSame(1, $menuPromotion->usage_count);
        $this->assertSame(0, $categoryPromotion->usage_count);
    }

    public function test_cashier_checkout_applies_selected_promotion_only(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $category = Category::create([
            'name' => 'Non Coffee',
            'slug' => 'non-coffee',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Matcha Latte',
            'slug' => 'matcha-latte',
            'description' => null,
            'price' => 10000,
            'cashback' => 1000,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $categoryPromotion = Promotion::create([
            'name' => 'Category 1000',
            'type' => Promotion::TYPE_FIXED_AMOUNT,
            'discount_value' => 1000,
            'min_purchase' => null,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        $categoryPromotion->rules()->create([
            'applicable_type' => 'category',
            'applicable_id' => $category->id,
        ]);

        $menuPromotion = Promotion::create([
            'name' => 'Menu 20%',
            'type' => Promotion::TYPE_PERCENTAGE,
            'discount_value' => 20,
            'min_purchase' => null,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        $menuPromotion->rules()->create([
            'applicable_type' => 'menu',
            'applicable_id' => $menu->id,
        ]);

        $response = $this->actingAs($cashier)->post('/cashier/pesanan-baru', [
            'payment_method' => 'cash',
            'customer_name' => 'Walk In',
            'is_mahasiswa' => true,
            'promotion_ids' => [$menuPromotion->id],
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect();

        $order = Order::with('appliedPromotions')->latest('id')->firstOrFail();

        $this->assertSame(Order::STATUS_DIPROSES, $order->status);
        $this->assertTrue($order->is_paid);
        $this->assertSame(14400.0, (float) $order->total_amount);
        $this->assertCount(1, $order->appliedPromotions);

        $appliedPromotion = $order->appliedPromotions->first();

        $this->assertSame($menuPromotion->id, $appliedPromotion->promotion_id);
        $this->assertSame(3600.0, (float) $appliedPromotion->discount_amount);

        $menuPromotion->refresh();
        $categoryPromotion->refresh();

        $this->assertSame(1, $menuPromotion->usage_count);
        $this->assertSame(0, $categoryPromotion->usage_count);
    }
}
