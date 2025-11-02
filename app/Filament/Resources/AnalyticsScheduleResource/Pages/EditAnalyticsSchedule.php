<?php

namespace App\Filament\Resources\AnalyticsScheduleResource\Pages;

use App\Filament\Resources\AnalyticsScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnalyticsSchedule extends EditRecord
{
    protected static string $resource = AnalyticsScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
