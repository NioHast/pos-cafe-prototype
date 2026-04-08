<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class PromotionService
{
    /**
     * Get all active promotions that can apply to the given menu.
     */
    public function getApplicablePromotionsForMenu(Menu $menu): Collection
    {
        return Promotion::query()
            ->with('rules')
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get()
            ->filter(fn (Promotion $promotion) =>
                $promotion->canBeUsed() && $promotion->isApplicableTo((int) $menu->id, (int) $menu->category_id)
            )
            ->values();
    }

    /**
     * Calculate discount amount for one promotion against a line item.
     */
    public function calculateDiscountAmount(Promotion $promotion, float $unitPrice, int $quantity): float
    {
        $lineBase = $unitPrice * $quantity;

        if ($promotion->min_purchase !== null && $lineBase < (float) $promotion->min_purchase) {
            return 0;
        }

        return match ($promotion->type) {
            'percentage' => min($lineBase, $lineBase * ((float) $promotion->discount_value / 100)),
            'fixed_amount' => min($lineBase, (float) $promotion->discount_value),
            default => 0,
        };
    }
}
