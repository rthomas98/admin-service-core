<?php

namespace App\Filament\Resources\Trailers\Pages;

use App\Filament\Resources\TrailerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrailers extends ListRecords
{
    protected static string $resource = TrailerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
