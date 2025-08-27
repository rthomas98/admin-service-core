<?php

namespace App\Filament\Resources\ServiceAreas\Pages;

use App\Filament\Resources\ServiceAreas\ServiceAreaResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceArea extends CreateRecord
{
    protected static string $resource = ServiceAreaResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        
        return $data;
    }
}
