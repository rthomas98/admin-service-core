<?php

namespace App\Filament\Resources\MaintenanceLogs\Tables;

use App\Filament\Resources\MaintenanceLogs\MaintenanceLogResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaintenanceLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('equipment.id')
                    ->searchable(),
                TextColumn::make('technician.name')
                    ->searchable(),
                TextColumn::make('service_type')
                    ->searchable(),
                TextColumn::make('service_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('end_time')
                    ->time()
                    ->sortable(),
                TextColumn::make('service_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('parts_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('labor_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('condition_before')
                    ->searchable(),
                TextColumn::make('condition_after')
                    ->searchable(),
                IconColumn::make('requires_followup')
                    ->boolean(),
                TextColumn::make('next_service_date')
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
                fn ($record) => MaintenanceLogResource::getUrl('view', ['record' => $record])
            );
    }
}
