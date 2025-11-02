<?php

namespace App\Filament\Resources\AnalyticsScheduleResource\Pages;

use App\Filament\Resources\AnalyticsScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAnalyticsSchedule extends ViewRecord
{
    protected static string $resource = AnalyticsScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
