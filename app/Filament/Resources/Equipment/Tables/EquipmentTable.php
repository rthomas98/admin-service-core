<?php

namespace App\Filament\Resources\Equipment\Tables;

use App\Filament\Resources\Equipment\EquipmentResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('size')
                    ->searchable(),
                TextColumn::make('unit_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('condition')
                    ->searchable(),
                TextColumn::make('current_location')
                    ->searchable(),
                TextColumn::make('latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('last_service_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('next_service_due')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('color')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn ($record) => EquipmentResource::getUrl('view', ['record' => $record])
            );
    }
}
