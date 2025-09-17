<?php

namespace App\Filament\Resources\DisposalSites\Pages;

use App\Filament\Resources\DisposalSites\DisposalSiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDisposalSite extends EditRecord
{
    protected static string $resource = DisposalSiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
