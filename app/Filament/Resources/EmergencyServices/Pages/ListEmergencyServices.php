<?php

namespace App\Filament\Resources\EmergencyServices\Pages;

use App\Filament\Resources\EmergencyServices\EmergencyServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmergencyServices extends ListRecords
{
    protected static string $resource = EmergencyServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
