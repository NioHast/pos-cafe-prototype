<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Support\Collection;

class OrderCalculationService
{
    public function __construct(
        protected PromotionService $promotionService,
    ) {}

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
    public function calculateItem(
        Menu $menu,
        int $quantity,
        ?User $customer = null,
        array $selectedPromotionIds = [],
        string $promotionStrategy = 'single_best',
        bool $includePromotionMeta = false,
    ): array
    {
        $isStudent = $customer?->isStudent() ?? false;

        // Student gets student_price (fallback to regular price if not set)
        $price = $isStudent
            ? ($menu->student_price ?? $menu->price)
            : $menu->price;

        $basePrice = $menu->price;
        $discountAmount = 0;
        $discountName = null;
        $appliedPromotions = [];

        $applicablePromotions = $this->promotionService->getApplicablePromotionsForMenu($menu);

        if (!empty($selectedPromotionIds)) {
            $selectedMap = collect($selectedPromotionIds)->map(fn ($id) => (int) $id)->flip();
            $applicablePromotions = $applicablePromotions
                ->filter(fn (Promotion $promotion) => $selectedMap->has((int) $promotion->id))
                ->values();
        }

        if ($applicablePromotions->isNotEmpty()) {
            if ($promotionStrategy === 'stacking') {
                $remainingBase = (float) ($price * $quantity);

                foreach ($applicablePromotions as $promotion) {
                    if ($remainingBase <= 0) {
                        break;
                    }

                    $currentUnitPrice = $quantity > 0 ? $remainingBase / $quantity : 0;
                    $promoDiscount = $this->promotionService->calculateDiscountAmount($promotion, $currentUnitPrice, $quantity);
                    $promoDiscount = min($remainingBase, $promoDiscount);

                    if ($promoDiscount <= 0) {
                        continue;
                    }

                    $remainingBase -= $promoDiscount;
                    $discountAmount += $promoDiscount;
                    $appliedPromotions[] = [
                        'promotion_id' => $promotion->id,
                        'discount_type' => $promotion->type,
                        'discount_value' => (float) $promotion->discount_value,
                        'discount_amount' => $promoDiscount,
                        'name' => $promotion->name,
                    ];
                }

                $discountName = collect($appliedPromotions)->pluck('name')->implode(', ');
            } else {
                $bestPromotion = $this->getBestPromotion($applicablePromotions, (float) $price, $quantity);

                if ($bestPromotion !== null) {
                    $discountAmount = $this->promotionService
                        ->calculateDiscountAmount($bestPromotion, (float) $price, $quantity);
                    $discountName = $bestPromotion->name;
                    $appliedPromotions[] = [
                        'promotion_id' => $bestPromotion->id,
                        'discount_type' => $bestPromotion->type,
                        'discount_value' => (float) $bestPromotion->discount_value,
                        'discount_amount' => $discountAmount,
                        'name' => $bestPromotion->name,
                    ];
                }
            }
        }

        $lineTotal = ($price * $quantity) - $discountAmount;

        $result = [
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

        if ($includePromotionMeta) {
            $result['applied_promotions'] = $appliedPromotions;
        }

        return $result;
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

    private function getBestPromotion(Collection $promotions, float $unitPrice, int $quantity): ?Promotion
    {
        $best = null;
        $bestDiscount = 0;

        foreach ($promotions as $promotion) {
            $discount = $this->promotionService->calculateDiscountAmount($promotion, $unitPrice, $quantity);
            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $best = $promotion;
            }
        }

        return $best;
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
