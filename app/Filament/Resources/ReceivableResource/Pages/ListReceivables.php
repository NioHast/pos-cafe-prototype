<?php

namespace App\Filament\Resources\ReceivableResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\ReceivableResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReceivables extends ListRecords
{
    protected static string $resource = ReceivableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}