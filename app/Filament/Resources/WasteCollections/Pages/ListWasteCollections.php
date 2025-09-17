<?php

namespace App\Filament\Resources\WasteCollections\Pages;

use App\Filament\Resources\WasteCollections\WasteCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWasteCollections extends ListRecords
{
    protected static string $resource = WasteCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
