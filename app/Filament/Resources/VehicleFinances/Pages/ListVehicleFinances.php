<?php

namespace App\Filament\Resources\VehicleFinances\Pages;

use App\Filament\Resources\VehicleFinanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleFinances extends ListRecords
{
    protected static string $resource = VehicleFinanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
