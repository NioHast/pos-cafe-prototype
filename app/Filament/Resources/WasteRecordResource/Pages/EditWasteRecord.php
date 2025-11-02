<?php

namespace App\Filament\Resources\WasteRecordResource\Pages;

use App\Filament\Resources\WasteRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWasteRecord extends EditRecord
{
    protected static string $resource = WasteRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
