<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotion_with_rule_can_be_created_updated_and_soft_deleted(): void
    {
        $category = Category::create([
            'name' => 'Promo Category',
            'slug' => 'promo-category',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Promo Menu',
            'slug' => 'promo-menu',
            'description' => null,
            'price' => 20000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $promotion = Promotion::create([
            'name' => 'Promo 10%',
            'type' => Promotion::TYPE_PERCENTAGE,
            'discount_value' => 10,
            'min_purchase' => 10000,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'status' => Promotion::STATUS_ACTIVE,
            'description' => 'Promo test',
            'usage_limit' => 100,
            'usage_count' => 0,
        ]);

        $promotion->rules()->create([
            'applicable_type' => 'menu',
            'applicable_id' => $menu->id,
        ]);

        $this->assertDatabaseHas('promotions', [
            'id' => $promotion->id,
            'name' => 'Promo 10%',
        ]);

        $this->assertDatabaseHas('promotion_rules', [
            'promotion_id' => $promotion->id,
            'applicable_type' => 'menu',
            'applicable_id' => $menu->id,
        ]);

        $promotion->update([
            'usage_count' => 3,
            'status' => Promotion::STATUS_INACTIVE,
        ]);

        $this->assertDatabaseHas('promotions', [
            'id' => $promotion->id,
            'usage_count' => 3,
            'status' => Promotion::STATUS_INACTIVE,
        ]);

        $promotion->delete();

        $this->assertSoftDeleted('promotions', [
            'id' => $promotion->id,
        ]);
    }

    public function test_non_admin_cannot_access_promotion_admin_resource(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $response = $this->actingAs($cashier)
            ->get(route('filament.admin.resources.promotions.index'));

        $this->assertNotSame(200, $response->getStatusCode());
    }
}