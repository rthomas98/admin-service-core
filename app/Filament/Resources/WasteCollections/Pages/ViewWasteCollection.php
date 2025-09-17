<?php

namespace App\Filament\Resources\WasteCollections\Pages;

use App\Filament\Resources\WasteCollections\WasteCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWasteCollection extends ViewRecord
{
    protected static string $resource = WasteCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
