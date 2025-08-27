<?php

namespace App\Filament\Resources\DeliverySchedules\Pages;

use App\Filament\Resources\DeliverySchedules\DeliveryScheduleResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliverySchedule extends CreateRecord
{
    protected static string $resource = DeliveryScheduleResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        
        return $data;
    }
}
