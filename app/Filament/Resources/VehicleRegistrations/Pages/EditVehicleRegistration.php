<?php

namespace App\Filament\Resources\VehicleRegistrations\Pages;

use App\Filament\Resources\VehicleRegistrations\VehicleRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleRegistration extends EditRecord
{
    protected static string $resource = VehicleRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
