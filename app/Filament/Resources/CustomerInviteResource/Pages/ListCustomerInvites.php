<?php

namespace App\Filament\Resources\CustomerInviteResource\Pages;

use App\Filament\Resources\CustomerInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomerInvites extends ListRecords
{
    protected static string $resource = CustomerInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return 'Customer Portal Invitations';
    }

    public function getSubheading(): ?string
    {
        return 'Invite customers to access their portal where they can view invoices, submit service requests, and manage their account.';
    }
}
