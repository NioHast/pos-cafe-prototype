<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use App\Services\StockReconciliationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        /** @var StockReconciliationService $service */
        $service = app(StockReconciliationService::class);

        return $service->createManualAdjustment(
            ingredientId: (int) $data['ingredient_id'],
            quantity: (float) $data['quantity'],
            adjustmentType: (string) $data['adjustment_type'],
            reason: (string) $data['reason'],
            recordedBy: Auth::id(),
            reference: $data['reference'] ?? null,
            approvedBy: $data['approved_by'] ?? null,
            ingredientBatchId: $data['ingredient_batch_id'] ?? null,
        );
    }
}