<?php

namespace App\Filament\Resources\VehicleFinances\Pages;

use App\Filament\Resources\VehicleFinanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleFinance extends EditRecord
{
    protected static string $resource = VehicleFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
