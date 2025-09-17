<?php

namespace App\Filament\Resources\CustomerInviteResource\Pages;

use App\Filament\Resources\CustomerInviteResource;
use App\Mail\CustomerInvitationMail;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateCustomerInvite extends CreateRecord
{
    protected static string $resource = CustomerInviteResource::class;

    public function getTitle(): string
    {
        return 'Send New Customer Portal Invitation';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $tenant = Filament::getTenant();

        $data['company_id'] = $tenant->id;
        $data['invited_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $invite = $this->record;

        // Generate registration URL
        $registrationUrl = route('customer.register.form', [
            'token' => $invite->token,
        ]);

        try {
            // Send the invitation email
            Mail::to($invite->email)->send(
                new CustomerInvitationMail($invite, $registrationUrl)
            );

            Notification::make()
                ->title('Invitation Sent Successfully')
                ->body("Portal invitation has been sent to {$invite->email}.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            // If email fails, delete the invitation
            $invite->delete();

            Notification::make()
                ->title('Failed to Send Invitation')
                ->body('There was an error sending the invitation email. The invitation has been deleted.')
                ->danger()
                ->send();

            // Redirect back to the list
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
