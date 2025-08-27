<?php

namespace App\Filament\Resources\FinanceCompanies\Pages;

use App\Filament\Resources\FinanceCompanies\FinanceCompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFinanceCompany extends EditRecord
{
    protected static string $resource = FinanceCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
