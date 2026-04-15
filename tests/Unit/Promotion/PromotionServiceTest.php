<?php

namespace Tests\Unit\Promotion;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_applicable_promotions_for_menu_returns_matching_active_promotions(): void
    {
        $category = Category::create([
            'name' => 'Category A',
            'slug' => 'category-a',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu A',
            'slug' => 'menu-a',
            'description' => null,
            'price' => 30000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $matchPromotion = Promotion::create([
            'name' => 'Match Promo',
            'type' => Promotion::TYPE_PERCENTAGE,
            'discount_value' => 15,
            'min_purchase' => null,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => 10,
            'usage_count' => 0,
        ]);

        $matchPromotion->rules()->create([
            'applicable_type' => 'category',
            'applicable_id' => $category->id,
        ]);

        $otherPromotion = Promotion::create([
            'name' => 'Other Promo',
            'type' => Promotion::TYPE_PERCENTAGE,
            'discount_value' => 20,
            'min_purchase' => null,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => 1,
            'usage_count' => 1,
        ]);

        $otherPromotion->rules()->create([
            'applicable_type' => 'menu',
            'applicable_id' => $menu->id,
        ]);

        $service = app(PromotionService::class);
        $result = $service->getApplicablePromotionsForMenu($menu);

        $this->assertCount(1, $result);
        $this->assertSame($matchPromotion->id, $result->first()->id);
    }

    public function test_calculate_discount_amount_handles_percentage_fixed_and_min_purchase(): void
    {
        $service = app(PromotionService::class);

        $percentage = Promotion::create([
            'name' => 'Percentage',
            'type' => Promotion::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'min_purchase' => 10000,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        $fixed = Promotion::create([
            'name' => 'Fixed',
            'type' => Promotion::TYPE_FIXED_AMOUNT,
            'discount_value' => 7000,
            'min_purchase' => null,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'usage_limit' => null,
            'usage_count' => 0,
        ]);

        $this->assertSame(3000.0, $service->calculateDiscountAmount($percentage, 15000, 2));
        $this->assertSame(7000.0, $service->calculateDiscountAmount($fixed, 10000, 1));
        $this->assertSame(0.0, $service->calculateDiscountAmount($percentage, 5000, 1));
    }
}