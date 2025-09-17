<?php

namespace App\Filament\Resources\DisposalSites\Pages;

use App\Filament\Resources\DisposalSites\DisposalSiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDisposalSites extends ListRecords
{
    protected static string $resource = DisposalSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
