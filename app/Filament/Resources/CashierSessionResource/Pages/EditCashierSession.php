<?php

namespace App\Filament\Resources\CashierSessionResource\Pages;

use App\Filament\Resources\CashierSessionResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCashierSession extends EditRecord
{
    protected static string $resource = CashierSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
