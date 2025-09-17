<?php

namespace App\Filament\Resources\CustomerInviteResource\Pages;

use App\Filament\Resources\CustomerInviteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerInvite extends EditRecord
{
    protected static string $resource = CustomerInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->label('Cancel Invitation')
                ->modalHeading('Cancel Invitation')
                ->modalDescription('This will permanently cancel this invitation.'),
        ];
    }

    public function getTitle(): string
    {
        return "Edit Invitation to {$this->record->email}";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
