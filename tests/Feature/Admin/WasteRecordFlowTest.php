<?php

namespace Tests\Feature\Admin;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\WasteRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WasteRecordFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_waste_record_creation_reduces_stock_and_creates_audit_movement(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Susu Waste Test',
            'unit' => 'ml',
            'low_stock_threshold' => 100,
            'is_active' => true,
        ]);

        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
            'expiry_date' => now()->addDays(5)->toDateString(),
            'received_at' => now(),
            'cost_per_unit' => 1,
        ]);

        $this->actingAs($admin);

        $waste = WasteRecord::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 30,
            'reason' => 'Spillage',
            'recorded_by' => null,
        ]);

        $batch->refresh();

        $this->assertSame($admin->id, $waste->recorded_by);
        $this->assertSame(70.0, (float) $batch->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'waste_record_id' => $waste->id,
            'movement_type' => 'waste',
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_waste_record_is_saved_but_no_stock_movement_is_created_when_stock_is_insufficient(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Kopi Waste Insufficient',
            'unit' => 'gram',
            'low_stock_threshold' => 20,
            'is_active' => true,
        ]);

        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'expiry_date' => now()->addDays(10)->toDateString(),
            'received_at' => now(),
            'cost_per_unit' => 1,
        ]);

        $this->actingAs($admin);

        $waste = WasteRecord::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 10,
            'reason' => 'Failed deduction test',
            'recorded_by' => null,
        ]);

        $batch->refresh();

        $this->assertNotNull($waste->id);
        $this->assertSame(5.0, (float) $batch->quantity);
        $this->assertDatabaseMissing('stock_movements', [
            'waste_record_id' => $waste->id,
            'movement_type' => 'waste',
        ]);
    }
}
