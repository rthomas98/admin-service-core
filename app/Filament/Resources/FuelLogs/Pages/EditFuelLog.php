<?php

namespace App\Filament\Resources\FuelLogs\Pages;

use App\Filament\Resources\FuelLogs\FuelLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFuelLog extends EditRecord
{
    protected static string $resource = FuelLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}