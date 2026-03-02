<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Services\OrderCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected OrderCalculationService $calcService;
    protected User $admin;
    protected User $cashier;
    protected User $student;
    protected Menu $menuRegular;
    protected Menu $menuExpensive;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calcService = app(OrderCalculationService::class);

        // Seed roles
        $adminRole = Role::create(['name' => 'admin']);
        $cashierRole = Role::create(['name' => 'cashier']);
        $studentRole = Role::create(['name' => 'student']);

        // Create users
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'role_id' => $adminRole->id,
        ]);

        $this->cashier = User::factory()->create([
            'name' => 'Kasir Satu',
            'role_id' => $cashierRole->id,
        ]);

        $this->student = User::factory()->create([
            'name' => 'Andi Mahasiswa',
            'role_id' => $studentRole->id,
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'nim' => '2024001',
            'faculty' => 'Teknik',
            'major' => 'Informatika',
            'year' => 2024,
        ]);

        // Create menu items
        $category = Category::create(['name' => 'Beverages', 'is_active' => true]);

        $this->menuRegular = Menu::create([
            'name' => 'Kopi Susu',
            'price' => 15000,
            'student_price' => 12000,
            'status' => 'available',
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->menuExpensive = Menu::create([
            'name' => 'Cappuccino Premium',
            'price' => 35000,
            'student_price' => 28000,
            'status' => 'available',
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    // ──────────────────────────────────────
    // Customer Snapshot Tests
    // ──────────────────────────────────────

    public function test_student_order_uses_student_price_and_snapshots_name(): void
    {
        $this->actingAs($this->cashier);

        $snapshot = $this->calcService->snapshotCustomer($this->student);

        $this->assertEquals($this->student->id, $snapshot['customer_id']);
        $this->assertEquals('Andi Mahasiswa', $snapshot['customer_name']);
        $this->assertEquals('student', $snapshot['customer_type']);
    }

    public function test_guest_with_name_creates_proper_snapshot(): void
    {
        $snapshot = $this->calcService->snapshotCustomer(null, 'Budi Tamu');

        $this->assertNull($snapshot['customer_id']);
        $this->assertEquals('Budi Tamu', $snapshot['customer_name']);
        $this->assertEquals('guest', $snapshot['customer_type']);
    }

    public function test_anonymous_guest_creates_proper_snapshot(): void
    {
        $snapshot = $this->calcService->snapshotCustomer(null, null);

        $this->assertNull($snapshot['customer_id']);
        $this->assertNull($snapshot['customer_name']);
        $this->assertEquals('guest', $snapshot['customer_type']);
    }

    // ──────────────────────────────────────
    // Price Calculation Tests
    // ──────────────────────────────────────

    public function test_calculate_item_uses_regular_price_for_guest(): void
    {
        $result = $this->calcService->calculateItem($this->menuRegular, 2, null);

        $this->assertEquals('Kopi Susu', $result['product_name']);
        $this->assertEquals(15000, $result['price']);
        $this->assertEquals(15000, $result['base_price']);
        $this->assertEquals(30000, $result['line_total']); // 15000 × 2
        $this->assertEquals(0, $result['discount_amount']);
    }

    public function test_calculate_item_uses_student_price_for_student(): void
    {
        $result = $this->calcService->calculateItem($this->menuRegular, 3, $this->student);

        $this->assertEquals('Kopi Susu', $result['product_name']);
        $this->assertEquals(12000, $result['price']); // student_price
        $this->assertEquals(15000, $result['base_price']); // always regular
        $this->assertEquals(36000, $result['line_total']); // 12000 × 3
    }

    public function test_calculate_order_totals(): void
    {
        $this->actingAs($this->cashier);

        $order = Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_type' => 'guest',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_price' => 0,
        ]);

        // Add items
        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $this->menuRegular->id,
            'product_name' => 'Kopi Susu',
            'quantity' => 2,
            'price' => 15000,
            'base_price' => 15000,
            'price_at_transaction' => 15000,
            'line_total' => 30000,
            'handled_by' => $this->cashier->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $this->menuExpensive->id,
            'product_name' => 'Cappuccino Premium',
            'quantity' => 1,
            'price' => 35000,
            'base_price' => 35000,
            'price_at_transaction' => 35000,
            'line_total' => 35000,
            'handled_by' => $this->cashier->id,
        ]);

        $totals = $this->calcService->calculateOrderTotals($order);

        $this->assertEquals(65000, $totals['subtotal']); // 30000 + 35000
        $this->assertEquals(0, $totals['discount_total']);
        $this->assertEquals(0, $totals['tax_amount']);
        $this->assertEquals(65000, $totals['grand_total']);
    }

    // ──────────────────────────────────────
    // Snapshot Immutability Tests
    // ──────────────────────────────────────

    public function test_snapshot_does_not_change_when_menu_price_changes(): void
    {
        $this->actingAs($this->cashier);

        // Create order with current price
        $itemData = $this->calcService->calculateItem($this->menuRegular, 1, null);

        $order = Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_type' => 'guest',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_price' => 0,
        ]);

        $orderItem = OrderItem::create(array_merge($itemData, [
            'order_id' => $order->id,
            'handled_by' => $this->cashier->id,
        ]));

        // Change menu price
        $this->menuRegular->update(['price' => 20000, 'name' => 'Kopi Susu V2']);

        // Reload order item — snapshot should be unchanged
        $orderItem->refresh();

        $this->assertEquals(15000, $orderItem->price);
        $this->assertEquals('Kopi Susu', $orderItem->product_name);
    }

    public function test_snapshot_does_not_change_when_student_name_changes(): void
    {
        $this->actingAs($this->cashier);

        $snapshot = $this->calcService->snapshotCustomer($this->student);

        $order = Order::create(array_merge($snapshot, [
            'cashier_id' => $this->cashier->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_price' => 0,
        ]));

        // Student changes name
        $this->student->update(['name' => 'Andi Baru']);

        // Reload order — snapshot should be unchanged
        $order->refresh();

        $this->assertEquals('Andi Mahasiswa', $order->customer_name);
    }

    // ──────────────────────────────────────
    // Void Tests
    // ──────────────────────────────────────

    public function test_void_order_sets_audit_trail(): void
    {
        $this->actingAs($this->admin);

        $order = Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_type' => 'guest',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_price' => 15000,
            'grand_total' => 15000,
        ]);

        $this->assertFalse($order->isVoided());

        $order->void([
            'void_reason' => 'wrong_order',
            'void_notes' => 'Customer ordered wrong item',
        ]);

        $order->refresh();

        $this->assertTrue($order->isVoided());
        $this->assertEquals('wrong_order', $order->void_reason);
        $this->assertEquals('Customer ordered wrong item', $order->void_notes);
        $this->assertEquals($this->admin->id, $order->voided_by);
        $this->assertNotNull($order->voided_at);
        $this->assertEquals('refunded', $order->payment_status);
    }

    // ──────────────────────────────────────
    // Full Order Creation Flow
    // ──────────────────────────────────────

    public function test_full_guest_order_flow(): void
    {
        $this->actingAs($this->cashier);

        $customerSnapshot = $this->calcService->snapshotCustomer(null, 'Tamu Biasa');

        $order = Order::create(array_merge($customerSnapshot, [
            'cashier_id' => $this->cashier->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_price' => 0,
        ]));

        $item1 = $this->calcService->calculateItem($this->menuRegular, 2, null);
        OrderItem::create(array_merge($item1, [
            'order_id' => $order->id,
            'handled_by' => $this->cashier->id,
        ]));

        $item2 = $this->calcService->calculateItem($this->menuExpensive, 1, null);
        OrderItem::create(array_merge($item2, [
            'order_id' => $order->id,
            'handled_by' => $this->cashier->id,
        ]));

        OrderObserver::recalculateTotals($order);
        $order->refresh();

        $this->assertEquals('guest', $order->customer_type);
        $this->assertEquals('Tamu Biasa', $order->customer_name);
        $this->assertNull($order->customer_id);
        $this->assertEquals(65000, $order->grand_total); // (15000×2) + 35000
        $this->assertEquals(2, $order->items()->count());
    }

    public function test_full_student_order_flow(): void
    {
        $this->actingAs($this->cashier);

        $customerSnapshot = $this->calcService->snapshotCustomer($this->student);

        $order = Order::create(array_merge($customerSnapshot, [
            'cashier_id' => $this->cashier->id,
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'total_price' => 0,
        ]));

        $item1 = $this->calcService->calculateItem($this->menuRegular, 2, $this->student);
        OrderItem::create(array_merge($item1, [
            'order_id' => $order->id,
            'handled_by' => $this->cashier->id,
        ]));

        $item2 = $this->calcService->calculateItem($this->menuExpensive, 1, $this->student);
        OrderItem::create(array_merge($item2, [
            'order_id' => $order->id,
            'handled_by' => $this->cashier->id,
        ]));

        OrderObserver::recalculateTotals($order);
        $order->refresh();

        $this->assertEquals('student', $order->customer_type);
        $this->assertEquals('Andi Mahasiswa', $order->customer_name);
        $this->assertEquals($this->student->id, $order->customer_id);
        // Student prices: (12000×2) + 28000 = 52000
        $this->assertEquals(52000, $order->grand_total);
    }

    // ──────────────────────────────────────
    // Scope Tests
    // ──────────────────────────────────────

    public function test_scopes_work_correctly(): void
    {
        $this->actingAs($this->cashier);

        Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_type' => 'student',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_price' => 10000,
            'grand_total' => 10000,
        ]);

        Order::create([
            'cashier_id' => $this->cashier->id,
            'customer_type' => 'guest',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'total_price' => 20000,
            'grand_total' => 20000,
        ]);

        $this->assertEquals(1, Order::paid()->count());
        $this->assertEquals(2, Order::today()->count());
        $this->assertEquals(1, Order::ofCustomerType('student')->count());
        $this->assertEquals(1, Order::ofCustomerType('guest')->count());
        $this->assertEquals(2, Order::notVoided()->count());
    }
}
