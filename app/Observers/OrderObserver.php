<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\InventoryService;
use App\Services\OrderCalculationService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    public function __construct(
        protected OrderCalculationService $calculationService,
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Handle the Order "creating" event.
     *
     * Auto-snapshot customer data before the order is persisted.
     */
    public function creating(Order $order): void
    {
        // Only snapshot if customer_type is not already set
        // (allows manual override from service layer)
        if (empty($order->customer_type)) {
            $customer = $order->customer_id
                ? \App\Models\User::find($order->customer_id)
                : null;

            $snapshot = $this->calculationService->snapshotCustomer(
                $customer,
                $order->customer_name
            );

            $order->fill($snapshot);
        }
    }

    /**
     * Handle the Order "created" event.
     *
     * After all items are attached (done by the caller), recalculate totals
     * and reduce stock for menus that have recipes.
     *
     * NOTE: This fires right after the order row is inserted, BEFORE items exist.
     * The caller must explicitly call recalculateTotals() and reduceStock()
     * after creating items, or use the helper methods below.
     */
    public function created(Order $order): void
    {
        // Totals & stock reduction are handled by the caller (SimulateKasir / API)
        // because items haven't been created yet at this event.
        // See: OrderCalculationService::calculateOrderTotals()
        // See: OrderObserver::reduceStockForOrder()
    }

    /**
     * Recalculate and persist order totals.
     * Call this AFTER all order items have been created.
     */
    public static function recalculateTotals(Order $order): void
    {
        $service = app(OrderCalculationService::class);
        $totals = $service->calculateOrderTotals($order);
        $order->updateQuietly($totals);
    }

    /**
     * Reduce ingredient stock for order items that have recipes.
     * Call this AFTER all order items have been created.
     */
    public static function reduceStockForOrder(Order $order): void
    {
        $order->loadMissing('items.menu.menuIngredients');

        $itemsWithRecipe = $order->items
            ->filter(fn ($item) => $item->menu && $item->menu->menuIngredients->isNotEmpty())
            ->map(fn ($item) => [
                'menu_id' => $item->menu_id,
                'quantity' => $item->quantity,
            ])
            ->values()
            ->toArray();

        if (empty($itemsWithRecipe)) {
            return;
        }

        try {
            $inventoryService = app(InventoryService::class);
            $inventoryService->decreaseStockForOrder($itemsWithRecipe);
        } catch (\Exception $e) {
            Log::warning('Stock reduction failed for order ' . $order->id . ': ' . $e->getMessage());
            // Don't throw — order is already saved; stock issue is non-blocking
        }
    }
}
