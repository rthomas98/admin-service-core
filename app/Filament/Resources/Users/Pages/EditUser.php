<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Hash password if provided
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            // Remove password from data if empty
            unset($data['password']);
        }

        // Handle email_verified_at toggle
        if (isset($data['email_verified_at'])) {
            if ($data['email_verified_at'] === true) {
                $data['email_verified_at'] = $this->record->email_verified_at ?? now();
            } elseif ($data['email_verified_at'] === false) {
                $data['email_verified_at'] = null;
            }
        }

        return $data;
    }
}
