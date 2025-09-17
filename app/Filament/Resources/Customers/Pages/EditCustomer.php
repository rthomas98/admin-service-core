<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure we have proper tenant context
        $tenant = Filament::getTenant();

        // Check permissions before proceeding
        if (! static::$resource::canEdit($this->record)) {
            Notification::make()
                ->title('Access Denied')
                ->body('You do not have permission to edit this customer.')
                ->danger()
                ->send();

            // Log the permission issue for debugging
            \Log::warning('Customer edit permission denied', [
                'customer_id' => $this->record->id,
                'user_id' => auth()->id(),
                'tenant_id' => $tenant?->id,
            ]);

            return [];
        }

        // Ensure company_id is set correctly from tenant
        if ($tenant && ! isset($data['company_id'])) {
            $data['company_id'] = $tenant->id;
        }

        return parent::mutateFormDataBeforeSave($data);
    }

    protected function afterSave(): void
    {
        // Optional: Log successful saves for debugging
        if (config('app.debug')) {
            \Log::debug('Customer updated successfully', [
                'customer_id' => $this->record->id,
                'customer_name' => $this->record->getFullNameAttribute(),
            ]);
        }

        parent::afterSave();
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Override to add error handling for better user experience
        try {
            // Update the record
            $record->update($data);

            return $record;

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Customer update failed', [
                'customer_id' => $record->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            // Show user-friendly error message
            Notification::make()
                ->title('Update Failed')
                ->body('An error occurred while saving the customer. Please try again or contact support if the problem persists.')
                ->danger()
                ->send();

            throw $e;
        }
    }
}
