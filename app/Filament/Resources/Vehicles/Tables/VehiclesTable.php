<?php

namespace App\Filament\Resources\Vehicles\Tables;

use App\Enums\VehicleStatus;
use App\Enums\VehicleType;
use App\Enums\FuelType;
use App\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_number')
                    ->label('Unit #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('make')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state): string => $state?->color() ?? 'gray')
                    ->searchable(),
                TextColumn::make('license_plate')
                    ->label('License')
                    ->searchable(),
                TextColumn::make('registration_expiry')
                    ->label('Registration')
                    ->date()
                    ->sortable()
                    ->color(fn ($record): string => 
                        $record->registration_status === 'expired' ? 'danger' : 
                        ($record->registration_status === 'expiring_soon' ? 'warning' : 'gray')
                    ),
                TextColumn::make('odometer')
                    ->numeric()
                    ->sortable()
                    ->suffix(' mi'),
                TextColumn::make('fuel_type')
                    ->badge()
                    ->searchable(),
                IconColumn::make('is_leased')
                    ->boolean()
                    ->label('Leased'),
                TextColumn::make('vin')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('color')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('registration_state')
                    ->label('State')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('odometer_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fuel_capacity')
                    ->numeric()
                    ->suffix(' gal')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_price')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('purchase_vendor')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('lease_end_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('image_path')
                    ->label('Photo')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('unit_number')
            ->filters([
                SelectFilter::make('status')
                    ->options(VehicleStatus::class)
                    ->multiple(),
                SelectFilter::make('type')
                    ->options(VehicleType::class)
                    ->multiple(),
                SelectFilter::make('fuel_type')
                    ->options(FuelType::class)
                    ->multiple(),
                SelectFilter::make('is_leased')
                    ->label('Ownership')
                    ->options([
                        '1' => 'Leased',
                        '0' => 'Owned',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn ($record) => VehicleResource::getUrl('view', ['record' => $record])
            );
    }
}