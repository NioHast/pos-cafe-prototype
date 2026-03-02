<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class OrderCalculationService
{
    /**
     * Build snapshot data for a single order item.
     *
     * Determines the correct price (student vs regular) and creates
     * an immutable snapshot of the menu data at transaction time.
     *
     * @param  Menu      $menu     The menu item being ordered
     * @param  int       $quantity Number of items
     * @param  User|null $customer The customer (null = guest)
     * @return array     Snapshot data ready for OrderItem::create()
     */
    public function calculateItem(Menu $menu, int $quantity, ?User $customer = null): array
    {
        $isStudent = $customer?->isStudent() ?? false;

        // Student gets student_price (fallback to regular price if not set)
        $price = $isStudent
            ? ($menu->student_price ?? $menu->price)
            : $menu->price;

        $basePrice = $menu->price;
        $discountAmount = 0;
        $discountName = null;

        // TODO: Apply item-level promotions here in future
        // $activePromo = $this->findApplicablePromo($menu);
        // if ($activePromo) {
        //     $discountAmount = $this->calculatePromoDiscount($activePromo, $price, $quantity);
        //     $discountName = $activePromo->name;
        // }

        $lineTotal = ($price * $quantity) - $discountAmount;

        return [
            'product_name' => $menu->name,
            'menu_id' => $menu->id,
            'quantity' => $quantity,
            'price' => $price,
            'base_price' => $basePrice,
            'price_at_transaction' => $price, // legacy column compat
            'discount_amount' => $discountAmount,
            'discount_name' => $discountName,
            'line_total' => $lineTotal,
        ];
    }

    /**
     * Calculate order-level totals from its items.
     *
     * Should be called after all items have been created.
     *
     * @param  Order $order The order with items loaded
     * @return array Financial breakdown
     */
    public function calculateOrderTotals(Order $order): array
    {
        $order->loadMissing('items');

        $subtotal = $order->items->sum('line_total');
        $discountTotal = 0; // TODO: order-level promotions
        $taxAmount = 0;     // TODO: tax calculation if needed
        $grandTotal = $subtotal - $discountTotal + $taxAmount;

        return [
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'total_price' => $grandTotal, // keep legacy column in sync
        ];
    }

    /**
     * Build customer snapshot data for an order.
     *
     * Rules:
     * - customer_id filled → student, auto-snapshot name
     * - customer_id null + name given → guest with name
     * - customer_id null + no name → anonymous guest
     *
     * @param  User|null    $customer     The student user (or null)
     * @param  string|null  $guestName    Manual name input from cashier
     * @return array        Customer snapshot data
     */
    public function snapshotCustomer(?User $customer, ?string $guestName = null): array
    {
        if ($customer !== null) {
            // Student customer — auto-snapshot
            return [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'customer_type' => 'student',
            ];
        }

        // Guest customer (with or without name)
        return [
            'customer_id' => null,
            'customer_name' => $guestName ?: null,
            'customer_type' => 'guest',
        ];
    }
}
