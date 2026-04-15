<?php

namespace Tests\Unit\Inventory;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_total_stock_sums_all_batches(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk Test',
            'unit' => 'gram',
            'low_stock_threshold' => 100,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 120,
            'expiry_date' => now()->addDays(7)->toDateString(),
            'received_at' => now()->subDays(2),
            'cost_per_unit' => 2,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 80,
            'expiry_date' => now()->addDays(14)->toDateString(),
            'received_at' => now()->subDay(),
            'cost_per_unit' => 2,
        ]);

        $this->assertSame(200.0, $ingredient->getTotalStock());
    }

    public function test_scope_active_returns_only_active_ingredients(): void
    {
        Ingredient::create([
            'name' => 'Susu Aktif',
            'unit' => 'ml',
            'low_stock_threshold' => 100,
            'is_active' => true,
        ]);

        Ingredient::create([
            'name' => 'Susu Nonaktif',
            'unit' => 'ml',
            'low_stock_threshold' => 100,
            'is_active' => false,
        ]);

        $this->assertCount(1, Ingredient::active()->get());
    }
}
