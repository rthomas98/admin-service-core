<?php

namespace App\Filament\Resources\DisposalSites\Pages;

use App\Filament\Resources\DisposalSites\DisposalSiteResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDisposalSite extends ViewRecord
{
    protected static string $resource = DisposalSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
