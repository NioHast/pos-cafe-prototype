<?php

namespace Tests\Feature\Admin;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingredient_can_be_created_updated_and_soft_deleted(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Susu Test',
            'unit' => 'ml',
            'low_stock_threshold' => 500,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => 'Susu Test',
        ]);

        $ingredient->update([
            'low_stock_threshold' => 650,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'low_stock_threshold' => 650,
            'is_active' => false,
        ]);

        $ingredient->delete();
        $this->assertSoftDeleted('ingredients', ['id' => $ingredient->id]);
    }

    public function test_non_admin_cannot_access_ingredient_admin_resource(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $response = $this->actingAs($cashier)
            ->get(route('filament.admin.resources.ingredients.index'));

        $this->assertNotSame(200, $response->getStatusCode());
    }
}
