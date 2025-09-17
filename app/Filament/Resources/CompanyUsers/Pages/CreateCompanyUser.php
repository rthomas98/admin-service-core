<?php

namespace App\Filament\Resources\CompanyUsers\Pages;

use App\Filament\Resources\CompanyUsers\CompanyUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompanyUser extends CreateRecord
{
    protected static string $resource = CompanyUserResource::class;
}
