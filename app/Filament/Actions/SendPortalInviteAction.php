<?php

namespace App\Filament\Actions;

use App\Mail\CustomerInvitationMail;
use App\Models\Customer;
use App\Models\CustomerInvite;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class SendPortalInviteAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendPortalInvite';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Send Portal Invite')
            ->icon('heroicon-o-envelope')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Send Customer Portal Invitation')
            ->modalDescription('Send an invitation email to allow this customer to register for portal access.')
            ->form([
                TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->default(fn (Customer $record) => $record->getNotificationEmail())
                    ->helperText('The invitation will be sent to this email address.'),

                Select::make('validity_days')
                    ->label('Invitation Valid For')
                    ->options([
                        3 => '3 days',
                        7 => '7 days (recommended)',
                        14 => '14 days',
                        30 => '30 days',
                    ])
                    ->default(7)
                    ->required()
                    ->helperText('How long the invitation link will remain valid.'),

                DateTimePicker::make('custom_expiry')
                    ->label('Custom Expiry Date')
                    ->nullable()
                    ->helperText('Optional: Set a specific expiry date instead of using validity days.')
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $set('validity_days', null);
                        }
                    }),
            ])
            ->action(function (array $data, Customer $record): void {
                $tenant = Filament::getTenant();

                if (! $tenant) {
                    Notification::make()
                        ->title('Error')
                        ->body('No company context available.')
                        ->danger()
                        ->send();

                    return;
                }

                // Check if customer already has portal access
                if ($record->hasPortalAccess()) {
                    Notification::make()
                        ->title('Customer Already Has Access')
                        ->body('This customer already has active portal access. Consider resending login instructions instead.')
                        ->warning()
                        ->send();

                    return;
                }

                // Check for existing valid invitations
                $existingInvite = $record->invites()
                    ->forEmail($data['email'])
                    ->valid()
                    ->first();

                if ($existingInvite) {
                    Notification::make()
                        ->title('Invitation Already Sent')
                        ->body('A valid invitation has already been sent to this email address.')
                        ->warning()
                        ->send();

                    return;
                }

                // Calculate expiry date
                $expiresAt = $data['custom_expiry']
                    ? $data['custom_expiry']
                    : now()->addDays($data['validity_days'] ?? 7);

                // Create the invitation
                $invite = CustomerInvite::create([
                    'email' => $data['email'],
                    'customer_id' => $record->id,
                    'company_id' => $tenant->id,
                    'invited_by' => auth()->id(),
                    'expires_at' => $expiresAt,
                ]);

                // Generate registration URL
                $registrationUrl = route('customer.register.form', [
                    'token' => $invite->token,
                ]);

                try {
                    // Send the invitation email
                    Mail::to($data['email'])->send(
                        new CustomerInvitationMail($invite, $registrationUrl)
                    );

                    Notification::make()
                        ->title('Invitation Sent Successfully')
                        ->body("Portal invitation has been sent to {$data['email']}.")
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    // If email fails, delete the invitation
                    $invite->delete();

                    Notification::make()
                        ->title('Failed to Send Invitation')
                        ->body('There was an error sending the invitation email. Please try again.')
                        ->danger()
                        ->send();
                }
            })
            ->visible(fn (Customer $record) => ! $record->hasPortalAccess());
    }
}
