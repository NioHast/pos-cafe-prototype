<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\IngredientBatch;
use App\Models\StockMovement;
use App\Models\WasteRecord;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Decrease stock for an order based on menu items and their recipes.
     * Uses FIFO/FEFO logic (prioritizes batches with nearest expiry date or oldest received date).
     *
     * @param array $items Array of items with 'menu_id' and 'quantity'
     *                     Example: [['menu_id' => 1, 'quantity' => 2], ['menu_id' => 5, 'quantity' => 1]]
     * @throws Exception If stock is insufficient
     * @return array Summary of stock changes
     */
    public function decreaseStockForOrder(array $items): array
    {
        $stockChanges = [];

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                $menuId = $item['menu_id'];
                $quantity = $item['quantity'];
                $itemContext = [
                    'movement_type' => 'sale',
                    'source_type' => 'order_item',
                    'source_id' => isset($item['order_item_id']) ? (string) $item['order_item_id'] : null,
                    'order_id' => $item['order_id'] ?? null,
                    'order_item_id' => $item['order_item_id'] ?? null,
                    'recorded_by' => $item['recorded_by'] ?? null,
                    'reference' => $item['reference'] ?? null,
                ];

                // Load menu with its ingredients recipe
                $menu = Menu::with('menuIngredients.ingredient')->findOrFail($menuId);

                // For each ingredient in the recipe
                foreach ($menu->menuIngredients as $menuIngredient) {
                    $ingredient = $menuIngredient->ingredient;
                    $requiredQuantity = $menuIngredient->quantity_used * $quantity;
                    $deduction = $this->deductIngredientStock(
                        ingredientId: (int) $ingredient->id,
                        requiredQuantity: (float) $requiredQuantity,
                        context: array_merge($itemContext, [
                            'menu_id' => $menu->id,
                            'notes' => "Order usage for menu {$menu->name}",
                        ])
                    );

                    $stockChanges[] = [
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'total_deducted' => $requiredQuantity,
                        'unit' => $ingredient->unit,
                        'batches' => $deduction['batch_changes'],
                    ];
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Stok berhasil dikurangi',
                'changes' => $stockChanges,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Decrease stock for waste record and log movement entries.
     */
    public function decreaseStockForWasteRecord(WasteRecord $wasteRecord): array
    {
        return DB::transaction(function () use ($wasteRecord) {
            return $this->deductIngredientStock(
                ingredientId: (int) $wasteRecord->ingredient_id,
                requiredQuantity: (float) $wasteRecord->quantity,
                context: [
                    'movement_type' => 'waste',
                    'source_type' => 'waste_record',
                    'source_id' => (string) $wasteRecord->id,
                    'waste_record_id' => $wasteRecord->id,
                    'recorded_by' => $wasteRecord->recorded_by,
                    'reference' => 'WR-' . $wasteRecord->id,
                    'notes' => $wasteRecord->reason,
                ]
            );
        });
    }

    /**
     * Decrease stock for a specific ingredient directly.
     */
    public function decreaseStockForIngredient(int $ingredientId, float $quantity, array $context = []): array
    {
        return DB::transaction(function () use ($ingredientId, $quantity, $context) {
            return $this->deductIngredientStock($ingredientId, $quantity, $context);
        });
    }

    /**
     * Deduct ingredient stock from batches (FEFO/FIFO) and write stock movement rows.
     */
    private function deductIngredientStock(int $ingredientId, float $requiredQuantity, array $context = []): array
    {
        $ingredient = Ingredient::findOrFail($ingredientId);

        $batches = IngredientBatch::where('ingredient_id', $ingredientId)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->orderBy('received_at', 'asc')
            ->get();

        $totalAvailable = (float) $batches->sum('quantity');
        if ($totalAvailable < $requiredQuantity) {
            throw new Exception(
                "Stok tidak mencukupi untuk bahan '{$ingredient->name}'. " .
                "Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, " .
                "Tersedia: {$totalAvailable} {$ingredient->unit}"
            );
        }

        $remainingToDeduct = $requiredQuantity;
        $batchChanges = [];

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $before = (float) $batch->quantity;
            $deductFromThisBatch = min($before, $remainingToDeduct);
            $after = $before - $deductFromThisBatch;

            $batch->quantity = $after;
            $batch->save();

            $remainingToDeduct -= $deductFromThisBatch;

            StockMovement::create([
                'ingredient_id' => $ingredientId,
                'ingredient_batch_id' => $batch->id,
                'order_id' => $context['order_id'] ?? null,
                'order_item_id' => $context['order_item_id'] ?? null,
                'waste_record_id' => $context['waste_record_id'] ?? null,
                'stock_adjustment_id' => $context['stock_adjustment_id'] ?? null,
                'movement_type' => $context['movement_type'] ?? 'sale',
                'source_type' => $context['source_type'] ?? null,
                'source_id' => isset($context['source_id']) ? (string) $context['source_id'] : null,
                'quantity_before' => $before,
                'quantity_change' => -$deductFromThisBatch,
                'quantity_after' => $after,
                'unit_cost' => $batch->cost_per_unit,
                'reference' => $context['reference'] ?? null,
                'notes' => $context['notes'] ?? null,
                'recorded_by' => $context['recorded_by'] ?? null,
            ]);

            $batchChanges[] = [
                'batch_id' => $batch->id,
                'deducted' => $deductFromThisBatch,
                'remaining' => $after,
            ];
        }

        return [
            'ingredient_id' => $ingredient->id,
            'ingredient_name' => $ingredient->name,
            'total_deducted' => $requiredQuantity,
            'unit' => $ingredient->unit,
            'batch_changes' => $batchChanges,
        ];
    }

    /**
     * Check if an order can be fulfilled with current stock.
     *
     * @param array $items Array of items with 'menu_id' and 'quantity'
     * @return array Result with 'can_fulfill' boolean and details
     */
    public function canFulfillOrder(array $items): array
    {
        $insufficient = [];

        foreach ($items as $item) {
            $menuId = $item['menu_id'];
            $quantity = $item['quantity'];

            $menu = Menu::with('menuIngredients.ingredient')->findOrFail($menuId);

            foreach ($menu->menuIngredients as $menuIngredient) {
                $ingredient = $menuIngredient->ingredient;
                $requiredQuantity = $menuIngredient->quantity_used * $quantity;
                $availableQuantity = $ingredient->getTotalStock();

                if ($availableQuantity < $requiredQuantity) {
                    $insufficient[] = [
                        'ingredient_name' => $ingredient->name,
                        'required' => $requiredQuantity,
                        'available' => $availableQuantity,
                        'unit' => $ingredient->unit,
                    ];
                }
            }
        }

        return [
            'can_fulfill' => empty($insufficient),
            'insufficient_ingredients' => $insufficient,
        ];
    }
}
