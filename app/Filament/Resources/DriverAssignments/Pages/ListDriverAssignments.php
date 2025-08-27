<?php

namespace App\Filament\Resources\DriverAssignments\Pages;

use App\Filament\Resources\DriverAssignments\DriverAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDriverAssignments extends ListRecords
{
    protected static string $resource = DriverAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}