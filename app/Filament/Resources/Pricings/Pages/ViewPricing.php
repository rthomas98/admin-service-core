<?php

namespace App\Filament\Resources\Pricings\Pages;

use App\Filament\Resources\Pricings\PricingResource;
use App\Filament\Resources\Pricings\Schemas\PricingForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewPricing extends ViewRecord
{
    protected static string $resource = PricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->form(function ($record) {
                    return PricingForm::configure(\Filament\Schemas\Schema::make())
                        ->getComponents();
                })
                ->fillForm(fn ($record) => $record->toArray())
                ->action(function (array $data, $record) {
                    $record->update($data);
                })
                ->modalHeading('Edit Pricing')
                ->modalSubmitActionLabel('Save changes')
                ->modalCancelActionLabel('Cancel')
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge),
            DeleteAction::make(),
        ];
    }
}