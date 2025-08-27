<?php

namespace App\Filament\Resources\EmergencyServices\Pages;

use App\Filament\Resources\EmergencyServices\EmergencyServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmergencyService extends EditRecord
{
    protected static string $resource = EmergencyServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
