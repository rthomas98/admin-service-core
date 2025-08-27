<?php

namespace App\Filament\Resources\Quotes\Pages;

use App\Filament\Resources\Quotes\QuoteResource;
use App\Filament\Resources\Quotes\Schemas\QuoteForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewQuote extends ViewRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->form(function ($record) {
                    return QuoteForm::configure(\Filament\Schemas\Schema::make())
                        ->getComponents();
                })
                ->fillForm(fn ($record) => $record->toArray())
                ->action(function (array $data, $record) {
                    $record->update($data);
                })
                ->modalHeading('Edit Quote')
                ->modalSubmitActionLabel('Save changes')
                ->modalCancelActionLabel('Cancel')
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge),
            DeleteAction::make(),
        ];
    }
}