<?php

namespace App\Filament\Resources\TeamInvites\Pages;

use App\Filament\Resources\TeamInvites\TeamInviteResource;
use App\Mail\TeamInvitationMail;
use App\Models\TeamInvite;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateTeamInvite extends CreateRecord
{
    protected static string $resource = TeamInviteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate token and set inviter
        $data['token'] = TeamInvite::generateToken();
        $data['invited_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send invitation email
        $this->sendInvitationEmail($this->record);
    }

    protected function sendInvitationEmail(TeamInvite $invite): void
    {
        $registrationUrl = route('team.register', ['token' => $invite->token]);

        try {
            Mail::to($invite->email)->send(
                new TeamInvitationMail($invite, $registrationUrl)
            );

            Notification::make()
                ->title('Invitation Sent')
                ->body("An invitation has been sent to {$invite->email}.")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to Send Invitation')
                ->body('The invitation was created but the email could not be sent. You can resend it from the list view.')
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Team invitation created';
    }
}
