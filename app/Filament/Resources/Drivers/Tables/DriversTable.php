<?php

namespace App\Filament\Resources\Drivers\Tables;

use App\Filament\Resources\Drivers\DriverResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DriversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('first_name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('license_number')
                    ->searchable(),
                TextColumn::make('license_class')
                    ->searchable(),
                TextColumn::make('license_expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('vehicle_type')
                    ->searchable(),
                TextColumn::make('vehicle_registration')
                    ->searchable(),
                TextColumn::make('vehicle_make')
                    ->searchable(),
                TextColumn::make('vehicle_model')
                    ->searchable(),
                TextColumn::make('vehicle_year')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('can_lift_heavy')
                    ->boolean(),
                IconColumn::make('has_truck_crane')
                    ->boolean(),
                TextColumn::make('hourly_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shift_start_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('shift_end_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('hired_date')
                    ->date()
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
                fn ($record) => DriverResource::getUrl('view', ['record' => $record])
            );
    }
}
