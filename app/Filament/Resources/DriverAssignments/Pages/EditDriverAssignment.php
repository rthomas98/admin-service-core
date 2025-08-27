<?php

namespace App\Filament\Resources\DriverAssignments\Pages;

use App\Filament\Resources\DriverAssignments\DriverAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDriverAssignment extends EditRecord
{
    protected static string $resource = DriverAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}