<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use Filament\Actions\DeleteAction;
use App\Filament\Resources\IngredientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIngredient extends EditRecord
{
    protected static string $resource = IngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
