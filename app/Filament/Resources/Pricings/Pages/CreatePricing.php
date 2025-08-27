<?php

namespace App\Filament\Resources\Pricings\Pages;

use App\Filament\Resources\Pricings\PricingResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreatePricing extends CreateRecord
{
    protected static string $resource = PricingResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = Filament::getTenant()->id;
        
        return $data;
    }
}
