<?php

namespace App\Filament\Resources\Trailers\Pages;

use App\Filament\Resources\Trailers\TrailerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrailer extends EditRecord
{
    protected static string $resource = TrailerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
