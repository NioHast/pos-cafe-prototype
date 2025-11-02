<?php

namespace App\Filament\Resources\CashierSessionResource\Pages;

use App\Filament\Resources\CashierSessionResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewCashierSession extends ViewRecord
{
    protected static string $resource = CashierSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
