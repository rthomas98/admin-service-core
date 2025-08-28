<?php

namespace App\Filament\Resources\VehicleRegistrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('vehicle.id')
                    ->searchable(),
                TextColumn::make('registration_number')
                    ->searchable(),
                TextColumn::make('license_plate')
                    ->searchable(),
                TextColumn::make('registration_state')
                    ->searchable(),
                TextColumn::make('registration_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('renewal_reminder_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('auto_renew')
                    ->boolean(),
                TextColumn::make('last_renewal_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('next_renewal_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('renewal_notice_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('registration_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('renewal_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('late_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('other_fees')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_paid')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->searchable(),
                TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->searchable(),
                TextColumn::make('transaction_number')
                    ->searchable(),
                TextColumn::make('vin')
                    ->searchable(),
                TextColumn::make('make')
                    ->searchable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('color')
                    ->searchable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vehicle_class')
                    ->searchable(),
                TextColumn::make('fuel_type')
                    ->searchable(),
                TextColumn::make('registered_owner')
                    ->searchable(),
                TextColumn::make('owner_address')
                    ->searchable(),
                TextColumn::make('owner_city')
                    ->searchable(),
                TextColumn::make('owner_state')
                    ->searchable(),
                TextColumn::make('owner_zip')
                    ->searchable(),
                TextColumn::make('insurance_company')
                    ->searchable(),
                TextColumn::make('insurance_policy_number')
                    ->searchable(),
                TextColumn::make('insurance_expiry_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('dot_compliant')
                    ->boolean(),
                TextColumn::make('dot_number')
                    ->searchable(),
                TextColumn::make('mc_number')
                    ->searchable(),
                TextColumn::make('registration_document')
                    ->searchable(),
                TextColumn::make('insurance_document')
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
