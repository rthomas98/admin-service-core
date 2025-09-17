<?php

namespace App\Filament\Resources\CustomerInviteResource\Pages;

use App\Filament\Resources\CustomerInviteResource;
use App\Mail\CustomerInvitationMail;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Mail;

class ViewCustomerInvite extends ViewRecord
{
    protected static string $resource = CustomerInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => ! $this->record->isAccepted()),

            Actions\Action::make('resend')
                ->label('Resend Invitation')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Resend Invitation')
                ->modalDescription('This will send another invitation email and extend the expiry date by 7 days.')
                ->action(function () {
                    // Update expiry to extend the invitation
                    $this->record->update([
                        'expires_at' => now()->addDays(7),
                    ]);

                    // Generate registration URL
                    $registrationUrl = route('customer.register.form', [
                        'token' => $this->record->token,
                    ]);

                    try {
                        // Resend the invitation email
                        Mail::to($this->record->email)->send(
                            new CustomerInvitationMail($this->record, $registrationUrl)
                        );

                        Notification::make()
                            ->title('Invitation Resent')
                            ->body("Invitation has been resent to {$this->record->email}.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Failed to Resend')
                            ->body('There was an error resending the invitation email.')
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => ! $this->record->isAccepted()),

            Actions\Action::make('copy_link')
                ->label('Copy Registration Link')
                ->icon('heroicon-o-link')
                ->color('gray')
                ->action(function () {
                    $registrationUrl = route('customer.register.form', [
                        'token' => $this->record->token,
                    ]);

                    // This would typically use JavaScript to copy to clipboard
                    // For now, we'll show the URL in a notification
                    Notification::make()
                        ->title('Registration Link')
                        ->body($registrationUrl)
                        ->info()
                        ->persistent()
                        ->send();
                })
                ->visible(fn () => ! $this->record->isAccepted() && ! $this->record->isExpired()),

            Actions\DeleteAction::make()
                ->label('Cancel Invitation')
                ->modalHeading('Cancel Invitation')
                ->modalDescription('This will permanently cancel this invitation. The recipient will no longer be able to use this link to register.')
                ->visible(fn () => ! $this->record->isAccepted()),
        ];
    }

    public function getTitle(): string
    {
        return "Invitation to {$this->record->email}";
    }
}
