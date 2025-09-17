<?php

namespace App\Filament\Resources\CompanyUsers\Pages;

use App\Filament\Resources\CompanyUsers\CompanyUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyUsers extends ListRecords
{
    protected static string $resource = CompanyUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
