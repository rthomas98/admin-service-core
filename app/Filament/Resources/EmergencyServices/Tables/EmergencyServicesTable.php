<?php

namespace App\Filament\Resources\EmergencyServices\Tables;

use App\Filament\Resources\EmergencyServices\EmergencyServiceResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmergencyServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('emergency_number')
                    ->searchable(),
                TextColumn::make('request_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('urgency_level')
                    ->searchable(),
                TextColumn::make('emergency_type')
                    ->searchable(),
                TextColumn::make('location_address')
                    ->searchable(),
                TextColumn::make('location_city')
                    ->searchable(),
                TextColumn::make('location_parish')
                    ->searchable(),
                TextColumn::make('location_postal_code')
                    ->searchable(),
                TextColumn::make('location_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('location_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('required_by_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('assigned_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('dispatched_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('arrival_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completion_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('target_response_minutes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('actual_response_minutes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('assignedDriver.id')
                    ->searchable(),
                TextColumn::make('assignedTechnician.name')
                    ->searchable(),
                TextColumn::make('emergency_surcharge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('contact_phone')
                    ->searchable(),
                TextColumn::make('contact_name')
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
                fn ($record) => EmergencyServiceResource::getUrl('view', ['record' => $record])
            );
    }
}
