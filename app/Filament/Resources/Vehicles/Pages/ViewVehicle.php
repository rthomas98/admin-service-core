<?php

namespace App\Filament\Resources\Vehicles\Pages;

use App\Filament\Resources\Vehicles\VehicleResource;
use App\Filament\Resources\Vehicles\Schemas\VehicleForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->form(function ($record) {
                    return VehicleForm::configure(\Filament\Schemas\Schema::make())
                        ->getComponents();
                })
                ->fillForm(fn ($record) => $record->toArray())
                ->action(function (array $data, $record) {
                    $record->update($data);
                })
                ->modalHeading('Edit Vehicle')
                ->modalSubmitActionLabel('Save changes')
                ->modalCancelActionLabel('Cancel')
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge),
            DeleteAction::make(),
        ];
    }
}