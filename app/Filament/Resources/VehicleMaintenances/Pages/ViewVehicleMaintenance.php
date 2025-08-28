<?php

namespace App\Filament\Resources\VehicleMaintenances\Pages;

use App\Filament\Resources\VehicleMaintenances\VehicleMaintenanceResource;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicleMaintenance extends ViewRecord
{
    protected static string $resource = VehicleMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}