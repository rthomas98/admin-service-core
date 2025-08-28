<?php

namespace App\Filament\Resources\VehicleInspections\Pages;

use App\Filament\Resources\VehicleInspections\VehicleInspectionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleInspection extends ViewRecord
{
    protected static string $resource = VehicleInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}