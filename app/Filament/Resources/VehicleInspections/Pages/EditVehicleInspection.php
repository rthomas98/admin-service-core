<?php

namespace App\Filament\Resources\VehicleInspections\Pages;

use App\Filament\Resources\VehicleInspections\VehicleInspectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleInspection extends EditRecord
{
    protected static string $resource = VehicleInspectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
