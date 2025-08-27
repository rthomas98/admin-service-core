<?php

namespace App\Filament\Resources\ServiceSchedules\Pages;

use App\Filament\Resources\ServiceSchedules\ServiceScheduleResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceSchedule extends CreateRecord
{
    protected static string $resource = ServiceScheduleResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        
        return $data;
    }
}
