<?php

namespace App\Filament\Resources\CompanyUsers\Pages;

use App\Filament\Resources\CompanyUsers\CompanyUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyUser extends EditRecord
{
    protected static string $resource = CompanyUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
