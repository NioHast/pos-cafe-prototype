<?php

namespace App\Filament\Resources\CashierSessionResource\Pages;

use App\Filament\Resources\CashierSessionResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListCashierSessions extends ListRecords
{
    protected static string $resource = CashierSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
