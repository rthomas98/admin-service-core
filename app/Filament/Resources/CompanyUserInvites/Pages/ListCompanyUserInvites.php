<?php

namespace App\Filament\Resources\CompanyUserInvites\Pages;

use App\Filament\Resources\CompanyUserInvites\CompanyUserInviteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyUserInvites extends ListRecords
{
    protected static string $resource = CompanyUserInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Internal User & Company Owner Invitations';
    }

    public function getSubheading(): ?string
    {
        return 'Invite internal staff members or company owners. Company owners are customers who need to manage their business profile and complete onboarding.';
    }
}
