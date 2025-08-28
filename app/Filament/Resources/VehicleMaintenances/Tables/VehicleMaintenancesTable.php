<?php

namespace App\Filament\Resources\VehicleMaintenances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VehicleMaintenancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('vehicle.id')
                    ->searchable(),
                TextColumn::make('driver.id')
                    ->searchable(),
                TextColumn::make('maintenance_number')
                    ->searchable(),
                TextColumn::make('maintenance_type')
                    ->searchable(),
                TextColumn::make('priority')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('scheduled_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('scheduled_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('completed_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('completed_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('odometer_at_service')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('next_service_miles')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('next_service_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('service_provider')
                    ->searchable(),
                TextColumn::make('technician_name')
                    ->searchable(),
                TextColumn::make('work_order_number')
                    ->searchable(),
                TextColumn::make('labor_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('parts_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('other_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('invoice_number')
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->searchable(),
                IconColumn::make('under_warranty')
                    ->boolean(),
                TextColumn::make('warranty_claim_number')
                    ->searchable(),
                TextColumn::make('warranty_covered_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('vehicle_down_from')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('vehicle_down_to')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('total_downtime_hours')
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
