<?php

namespace App\Filament\Resources\ServiceAreas\Tables;

use App\Filament\Resources\ServiceAreas\ServiceAreaResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceAreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('delivery_surcharge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pickup_surcharge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('emergency_surcharge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('standard_delivery_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rush_delivery_hours')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rush_delivery_surcharge')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('priority')
                    ->numeric()
                    ->sortable(),
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
                fn ($record) => ServiceAreaResource::getUrl('view', ['record' => $record])
            );
    }
}
