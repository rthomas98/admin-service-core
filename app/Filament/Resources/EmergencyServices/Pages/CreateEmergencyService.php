<?php

namespace App\Filament\Resources\EmergencyServices\Pages;

use App\Filament\Resources\EmergencyServices\EmergencyServiceResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateEmergencyService extends CreateRecord
{
    protected static string $resource = EmergencyServiceResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        
        return $data;
    }
}
