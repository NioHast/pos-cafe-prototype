<?php

namespace App\Filament\Resources\IngredientResource\Pages;

use App\Filament\Resources\IngredientResource;
use App\Filament\Resources\IngredientResource\RelationManagers\BatchesRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;

class ViewIngredient extends ViewRecord
{
    protected static string $resource = IngredientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public function getRelationManagers(): array
    {
        return [
            BatchesRelationManager::class,
        ];
    }
}
