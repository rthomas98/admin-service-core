<?php

namespace App\Filament\Resources\MaintenanceLogs\Pages;

use App\Filament\Resources\MaintenanceLogs\MaintenanceLogResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceLog extends CreateRecord
{
    protected static string $resource = MaintenanceLogResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        
        return $data;
    }
}
