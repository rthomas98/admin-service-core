<?php

namespace App\Filament\Resources\WasteRoutes\Pages;

use App\Filament\Resources\WasteRoutes\WasteRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWasteRoutes extends ListRecords
{
    protected static string $resource = WasteRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
