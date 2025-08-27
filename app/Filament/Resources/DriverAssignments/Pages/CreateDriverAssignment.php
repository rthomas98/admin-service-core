<?php

namespace App\Filament\Resources\DriverAssignments\Pages;

use App\Filament\Resources\DriverAssignments\DriverAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDriverAssignment extends CreateRecord
{
    protected static string $resource = DriverAssignmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->current_company_id;
        
        return $data;
    }
}