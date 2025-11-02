<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\IngredientBatch;
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

                // Load menu with its ingredients recipe
                $menu = Menu::with('menuIngredients.ingredient')->findOrFail($menuId);

                // For each ingredient in the recipe
                foreach ($menu->menuIngredients as $menuIngredient) {
                    $ingredient = $menuIngredient->ingredient;
                    $requiredQuantity = $menuIngredient->quantity_used * $quantity;

                    // Get available batches ordered by FEFO/FIFO
                    // Priority: earliest expiry_date, then oldest received_at
                    $batches = IngredientBatch::where('ingredient_id', $ingredient->id)
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->orderBy('received_at', 'asc')
                        ->get();

                    // Check if total available stock is sufficient
                    $totalAvailable = $batches->sum('quantity');
                    if ($totalAvailable < $requiredQuantity) {
                        throw new Exception(
                            "Stok tidak mencukupi untuk bahan '{$ingredient->name}'. " .
                            "Dibutuhkan: {$requiredQuantity} {$ingredient->unit}, " .
                            "Tersedia: {$totalAvailable} {$ingredient->unit}"
                        );
                    }

                    // Deduct from batches using FIFO/FEFO
                    $remainingToDeduct = $requiredQuantity;
                    $batchChanges = [];

                    foreach ($batches as $batch) {
                        if ($remainingToDeduct <= 0) {
                            break;
                        }

                        $deductFromThisBatch = min($batch->quantity, $remainingToDeduct);
                        $batch->quantity -= $deductFromThisBatch;
                        $batch->save();

                        $remainingToDeduct -= $deductFromThisBatch;

                        $batchChanges[] = [
                            'batch_id' => $batch->id,
                            'deducted' => $deductFromThisBatch,
                            'remaining' => $batch->quantity,
                        ];
                    }

                    $stockChanges[] = [
                        'ingredient_id' => $ingredient->id,
                        'ingredient_name' => $ingredient->name,
                        'total_deducted' => $requiredQuantity,
                        'unit' => $ingredient->unit,
                        'batches' => $batchChanges,
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
