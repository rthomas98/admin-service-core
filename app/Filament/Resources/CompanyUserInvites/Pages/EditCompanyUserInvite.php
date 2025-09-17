<?php

namespace App\Filament\Resources\CompanyUserInvites\Pages;

use App\Filament\Resources\CompanyUserInvites\CompanyUserInviteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyUserInvite extends EditRecord
{
    protected static string $resource = CompanyUserInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
