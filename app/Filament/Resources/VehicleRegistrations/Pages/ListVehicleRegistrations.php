<?php

namespace App\Filament\Resources\VehicleRegistrations\Pages;

use App\Filament\Resources\VehicleRegistrations\VehicleRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleRegistrations extends ListRecords
{
    protected static string $resource = VehicleRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
