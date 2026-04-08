<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockReconciliationService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function getExpectedStock(int $ingredientId): float
    {
        $firstMovement = StockMovement::where('ingredient_id', $ingredientId)
            ->oldest('id')
            ->first();

        if (! $firstMovement) {
            return (float) Ingredient::findOrFail($ingredientId)->getTotalStock();
        }

        $delta = (float) StockMovement::where('ingredient_id', $ingredientId)->sum('quantity_change');

        return (float) $firstMovement->quantity_before + $delta;
    }

    public function calculateVariance(int $ingredientId, float $physicalCount): array
    {
        $expected = $this->getExpectedStock($ingredientId);
        $variance = $physicalCount - $expected;

        return [
            'expected' => round($expected, 2),
            'physical' => round($physicalCount, 2),
            'variance' => round($variance, 2),
            'shrinkage_percent' => $this->getShrinkagePercentage($expected, $physicalCount),
        ];
    }

    public function getShrinkagePercentage(float $expected, float $physical): float
    {
        if ($expected <= 0) {
            return 0.0;
        }

        return round((($expected - $physical) / $expected) * 100, 2);
    }

    public function createManualAdjustment(
        int $ingredientId,
        float $quantity,
        string $adjustmentType,
        string $reason,
        ?int $recordedBy = null,
        ?string $reference = null
    ): StockAdjustment {
        if ($quantity <= 0) {
            throw new RuntimeException('Quantity adjustment harus lebih dari 0.');
        }

        if (! in_array($adjustmentType, ['increase', 'decrease'], true)) {
            throw new RuntimeException('Adjustment type tidak valid.');
        }

        return DB::transaction(function () use ($ingredientId, $quantity, $adjustmentType, $reason, $recordedBy, $reference) {
            $ingredient = Ingredient::with('batches')->findOrFail($ingredientId);
            $quantityBefore = (float) $ingredient->getTotalStock();
            $batch = $ingredient->batches()->orderByDesc('received_at')->first();

            if (! $batch) {
                throw new RuntimeException('Tidak ada batch untuk bahan ini. Tambahkan batch terlebih dahulu.');
            }

            if ($adjustmentType === 'decrease') {
                $adjustment = StockAdjustment::create([
                    'ingredient_id' => $ingredientId,
                    'ingredient_batch_id' => null,
                    'adjustment_type' => $adjustmentType,
                    'quantity' => $quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityBefore,
                    'reason' => $reason,
                    'reference' => $reference,
                    'recorded_by' => $recordedBy,
                    'adjusted_at' => now(),
                ]);

                $this->inventoryService->decreaseStockForIngredient(
                    ingredientId: $ingredientId,
                    quantity: $quantity,
                    context: [
                        'movement_type' => 'adjustment_decrease',
                        'source_type' => 'stock_adjustment',
                        'source_id' => (string) $adjustment->id,
                        'stock_adjustment_id' => $adjustment->id,
                        'recorded_by' => $recordedBy,
                        'reference' => $reference,
                        'notes' => $reason,
                    ]
                );

                $quantityAfter = (float) Ingredient::findOrFail($ingredientId)->getTotalStock();

                $adjustment->update([
                    'quantity_after' => $quantityAfter,
                ]);

                return $adjustment;
            }

            $batchBefore = (float) $batch->quantity;
            $batch->quantity = $batchBefore + $quantity;
            $batch->save();

            $quantityAfter = (float) Ingredient::findOrFail($ingredientId)->getTotalStock();

            $adjustment = StockAdjustment::create([
                'ingredient_id' => $ingredientId,
                'ingredient_batch_id' => $batch->id,
                'adjustment_type' => $adjustmentType,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $reason,
                'reference' => $reference,
                'recorded_by' => $recordedBy,
                'adjusted_at' => now(),
            ]);

            StockMovement::create([
                'ingredient_id' => $ingredientId,
                'ingredient_batch_id' => $batch->id,
                'stock_adjustment_id' => $adjustment->id,
                'movement_type' => 'adjustment_increase',
                'source_type' => 'stock_adjustment',
                'source_id' => (string) $adjustment->id,
                'quantity_before' => $batchBefore,
                'quantity_change' => $quantity,
                'quantity_after' => (float) $batch->quantity,
                'unit_cost' => $batch->cost_per_unit,
                'reference' => $reference,
                'notes' => $reason,
                'recorded_by' => $recordedBy,
            ]);

            return $adjustment;
        });
    }
}
