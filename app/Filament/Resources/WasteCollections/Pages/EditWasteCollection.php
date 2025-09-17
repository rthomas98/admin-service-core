<?php

namespace App\Filament\Resources\WasteCollections\Pages;

use App\Filament\Resources\WasteCollections\WasteCollectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWasteCollection extends EditRecord
{
    protected static string $resource = WasteCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
