<?php

namespace App\Filament\Resources\DailyIngredientUsageResource\Pages;

use App\Filament\Resources\DailyIngredientUsageResource;
use Filament\Resources\Pages\ListRecords;

class ListDailyIngredientUsages extends ListRecords
{
    protected static string $resource = DailyIngredientUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
