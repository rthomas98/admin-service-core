<?php

namespace App\Filament\Resources\ServiceOrders\Pages;

use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceOrder extends CreateRecord
{
    protected static string $resource = ServiceOrderResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        
        return $data;
    }
}
