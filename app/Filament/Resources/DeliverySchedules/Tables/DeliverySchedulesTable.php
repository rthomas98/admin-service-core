<?php

namespace App\Filament\Resources\DeliverySchedules\Tables;

use App\Filament\Resources\DeliverySchedules\DeliveryScheduleResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliverySchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('serviceOrder.id')
                    ->searchable(),
                TextColumn::make('equipment.id')
                    ->searchable(),
                TextColumn::make('driver.full_name')
                    ->label('Driver')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('driver', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('scheduled_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('actual_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('delivery_address')
                    ->searchable(),
                TextColumn::make('delivery_city')
                    ->searchable(),
                TextColumn::make('delivery_parish')
                    ->searchable(),
                TextColumn::make('delivery_postal_code')
                    ->searchable(),
                TextColumn::make('delivery_latitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_longitude')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('signature')
                    ->searchable(),
                TextColumn::make('estimated_duration_minutes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('actual_duration_minutes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('travel_distance_km')
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
                fn ($record) => DeliveryScheduleResource::getUrl('view', ['record' => $record])
            );
    }
}
