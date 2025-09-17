<?php

namespace App\Filament\Resources\CompanyUserInvites\Pages;

use App\Filament\Resources\CompanyUserInvites\CompanyUserInviteResource;
use App\Mail\CompanyUserInviteEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateCompanyUserInvite extends CreateRecord
{
    protected static string $resource = CompanyUserInviteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate a unique token for the invitation
        $data['token'] = Str::random(32);

        // Set default expiration if not provided
        if (! isset($data['expires_at'])) {
            $data['expires_at'] = now()->addDays(7);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send the invitation email
        try {
            Mail::to($this->record->email)->send(new CompanyUserInviteEmail($this->record));

            Notification::make()
                ->title('Invitation Sent')
                ->body("An invitation has been sent to {$this->record->email}")
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Email Failed')
                ->body("Failed to send invitation email: {$e->getMessage()}")
                ->danger()
                ->send();

            \Log::error('Failed to send company user invitation', [
                'email' => $this->record->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Company user invitation created';
    }
}
