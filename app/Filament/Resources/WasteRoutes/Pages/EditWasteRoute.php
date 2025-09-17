<?php

namespace App\Filament\Resources\WasteRoutes\Pages;

use App\Filament\Resources\WasteRoutes\WasteRouteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWasteRoute extends EditRecord
{
    protected static string $resource = WasteRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
