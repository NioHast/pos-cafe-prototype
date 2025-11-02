<?php

namespace App\Filament\Resources\AnalyticsScheduleResource\Pages;

use App\Filament\Resources\AnalyticsScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\IconSize;

class ListAnalyticsSchedules extends ListRecords
{
    protected static string $resource = AnalyticsScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->icon('heroicon-o-plus')
                ->iconSize(IconSize::Small),
        ];
    }
}
