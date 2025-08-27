<?php

namespace App\Filament\Resources\FinanceCompanies\Pages;

use App\Filament\Resources\FinanceCompanies\FinanceCompanyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFinanceCompanies extends ListRecords
{
    protected static string $resource = FinanceCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
