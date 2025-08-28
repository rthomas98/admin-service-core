<?php

namespace App\Filament\Resources\VehicleMaintenances\Pages;

use App\Filament\Resources\VehicleMaintenances\VehicleMaintenanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleMaintenances extends ListRecords
{
    protected static string $resource = VehicleMaintenanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
