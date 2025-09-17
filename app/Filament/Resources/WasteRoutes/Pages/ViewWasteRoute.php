<?php

namespace App\Filament\Resources\WasteRoutes\Pages;

use App\Filament\Resources\WasteRoutes\WasteRouteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWasteRoute extends ViewRecord
{
    protected static string $resource = WasteRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
