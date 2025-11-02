<?php

namespace App\Filament\Resources\WasteRecordResource\Pages;

use App\Filament\Resources\WasteRecordResource;
use Filament\Resources\Pages\ListRecords;

class ListWasteRecords extends ListRecords
{
    protected static string $resource = WasteRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
