<?php

namespace App\Observers;

use App\Models\WasteRecord;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Log;

class WasteRecordObserver
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function created(WasteRecord $wasteRecord): void
    {
        try {
            $this->inventoryService->decreaseStockForWasteRecord($wasteRecord);
        } catch (\Throwable $e) {
            Log::warning('Waste stock deduction failed for record ' . $wasteRecord->id . ': ' . $e->getMessage());
        }
    }
}
