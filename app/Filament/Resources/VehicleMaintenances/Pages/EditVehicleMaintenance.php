<?php

namespace App\Filament\Resources\VehicleMaintenances\Pages;

use App\Filament\Resources\VehicleMaintenances\VehicleMaintenanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleMaintenance extends EditRecord
{
    protected static string $resource = VehicleMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
