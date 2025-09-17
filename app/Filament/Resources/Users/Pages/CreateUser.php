<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\UserWelcomeEmail;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?string $generatedPassword = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If no password provided, generate one
        if (empty($data['password'])) {
            $this->generatedPassword = $this->generateSecurePassword();
            $data['password'] = Hash::make($this->generatedPassword);
        } else {
            // Store the plain password before hashing for email
            $this->generatedPassword = $data['password'];
            $data['password'] = Hash::make($data['password']);
        }

        // Ensure email_verified_at is set if the toggle is true
        if (isset($data['email_verified_at']) && $data['email_verified_at'] === true) {
            $data['email_verified_at'] = now();
        } elseif (isset($data['email_verified_at']) && $data['email_verified_at'] === false) {
            $data['email_verified_at'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send welcome email with credentials
        if ($this->generatedPassword && $this->record instanceof User) {
            try {
                Mail::to($this->record->email)->send(
                    new UserWelcomeEmail(
                        user: $this->record,
                        temporaryPassword: $this->generatedPassword,
                        personalMessage: 'Welcome to our team! Please log in and change your password as soon as possible.'
                    )
                );

                Notification::make()
                    ->title('User Created Successfully')
                    ->body("Welcome email with login credentials has been sent to {$this->record->email}")
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                Notification::make()
                    ->title('User Created but Email Failed')
                    ->body("User was created successfully but the welcome email could not be sent. Password: {$this->generatedPassword}")
                    ->warning()
                    ->persistent()
                    ->send();
            }
        }
    }

    protected function generateSecurePassword(): string
    {
        // Generate a secure but memorable password
        $words = [
            'Spring', 'Summer', 'Autumn', 'Winter',
            'Mountain', 'Ocean', 'Forest', 'Desert',
            'Thunder', 'Lightning', 'Sunshine', 'Rainbow',
            'Crystal', 'Diamond', 'Emerald', 'Sapphire',
            'Phoenix', 'Dragon', 'Eagle', 'Tiger',
        ];

        $word1 = $words[array_rand($words)];
        $word2 = $words[array_rand($words)];
        $number = random_int(100, 999);
        $special = ['!', '@', '#', '$', '%', '&', '*'][array_rand(['!', '@', '#', '$', '%', '&', '*'])];

        return $word1.$word2.$number.$special;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
