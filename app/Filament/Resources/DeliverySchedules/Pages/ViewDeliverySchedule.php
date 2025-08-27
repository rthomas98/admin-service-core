<?php

namespace App\Filament\Resources\DeliverySchedules\Pages;

use App\Filament\Resources\DeliverySchedules\DeliveryScheduleResource;
use App\Filament\Resources\DeliverySchedules\Schemas\DeliveryScheduleForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewDeliverySchedule extends ViewRecord
{
    protected static string $resource = DeliveryScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->form(function ($record) {
                    return DeliveryScheduleForm::configure(\Filament\Schemas\Schema::make())
                        ->getComponents();
                })
                ->fillForm(fn ($record) => $record->toArray())
                ->action(function (array $data, $record) {
                    $record->update($data);
                })
                ->modalHeading('Edit Delivery Schedule')
                ->modalSubmitActionLabel('Save changes')
                ->modalCancelActionLabel('Cancel')
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge),
            DeleteAction::make(),
        ];
    }
}