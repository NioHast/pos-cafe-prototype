<?php

namespace App\Filament\Resources\WasteRecordResource\Pages;

use Filament\Actions\CreateAction;
use App\Filament\Resources\WasteRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWasteRecords extends ListRecords
{
    protected static string $resource = WasteRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
