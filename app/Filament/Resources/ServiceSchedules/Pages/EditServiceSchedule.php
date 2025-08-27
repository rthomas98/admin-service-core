<?php

namespace App\Filament\Resources\ServiceSchedules\Pages;

use App\Filament\Resources\ServiceSchedules\ServiceScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceSchedule extends EditRecord
{
    protected static string $resource = ServiceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
