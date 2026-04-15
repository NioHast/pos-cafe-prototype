<?php

namespace Tests\Feature\Admin;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientBatchCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_can_be_created_and_updated_for_ingredient(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Kopi Batch Test',
            'unit' => 'gram',
            'low_stock_threshold' => 50,
            'is_active' => true,
        ]);

        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
            'expiry_date' => now()->addDays(10)->toDateString(),
            'received_at' => now()->subDay(),
            'cost_per_unit' => 2,
        ]);

        $this->assertDatabaseHas('ingredient_batches', [
            'id' => $batch->id,
            'ingredient_id' => $ingredient->id,
        ]);

        $batch->update([
            'quantity' => 75,
        ]);

        $this->assertDatabaseHas('ingredient_batches', [
            'id' => $batch->id,
            'quantity' => 75,
        ]);
    }

    public function test_batches_are_sorted_by_expiry_then_received_for_fefo_fifo_reference(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Gula Batch Test',
            'unit' => 'gram',
            'low_stock_threshold' => 20,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 40,
            'expiry_date' => now()->addDays(30)->toDateString(),
            'received_at' => now()->subDays(2),
            'cost_per_unit' => 1,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 60,
            'expiry_date' => now()->addDays(7)->toDateString(),
            'received_at' => now()->subDays(1),
            'cost_per_unit' => 1,
        ]);

        $first = $ingredient->batches()
            ->orderBy('expiry_date')
            ->orderBy('received_at')
            ->first();

        $this->assertSame(60.0, (float) $first->quantity);
    }
}
