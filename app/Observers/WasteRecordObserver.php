<?php

namespace App\Observers;

use Throwable;
use App\Models\WasteRecord;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WasteRecordObserver
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function creating(WasteRecord $wasteRecord): void
    {
        if ($wasteRecord->recorded_by === null && Auth::check()) {
            $wasteRecord->recorded_by = Auth::id();
        }
    }

    public function created(WasteRecord $wasteRecord): void
    {
        try {
            $this->inventoryService->decreaseStockForWasteRecord($wasteRecord);
        } catch (Throwable $exception) {
            Log::warning('Waste stock deduction failed for record ' . $wasteRecord->id . ': ' . $exception->getMessage());
        }
    }
}
