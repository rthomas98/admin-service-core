<?php

namespace App\Filament\Resources\FuelLogs\Pages;

use App\Filament\Resources\FuelLogs\FuelLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFuelLog extends CreateRecord
{
    protected static string $resource = FuelLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->current_company_id;
        
        return $data;
    }
}