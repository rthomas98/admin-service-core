<?php

namespace App\Filament\Resources\FuelLogs\Tables;

use App\Filament\Resources\FuelLogs\FuelLogResource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FuelLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fuel_date')
                    ->dateTime()
                    ->sortable(),
                
                TextColumn::make('vehicle.full_description')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('driver.full_name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('fuel_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'warning',
                        'diesel' => 'success',
                        'premium' => 'info',
                        'def' => 'gray',
                    }),
                
                TextColumn::make('gallons')
                    ->numeric(2)
                    ->suffix(' gal')
                    ->sortable(),
                
                TextColumn::make('price_per_gallon')
                    ->money('USD')
                    ->suffix('/gal')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_cost')
                    ->money('USD')
                    ->sortable(),
                
                TextColumn::make('odometer_reading')
                    ->numeric()
                    ->suffix(' mi')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('mpg')
                    ->label('MPG')
                    ->numeric(1)
                    ->sortable()
                    ->toggleable(),
                
                IconColumn::make('is_personal')
                    ->boolean()
                    ->toggleable(),
                
                TextColumn::make('fuel_station')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                ImageColumn::make('receipt_image')
                    ->label('Receipt')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('fuel_date', 'desc')
            ->filters([
                SelectFilter::make('fuel_type')
                    ->options([
                        'regular' => 'Regular',
                        'diesel' => 'Diesel',
                        'premium' => 'Premium',
                        'def' => 'DEF',
                    ]),
                
                Filter::make('is_personal')
                    ->label('Personal Use')
                    ->query(fn (Builder $query): Builder => $query->where('is_personal', true)),
                
                SelectFilter::make('vehicle_id')
                    ->relationship('vehicle', 'vin')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->year} {$record->make} {$record->model}")
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('driver_id')
                    ->relationship('driver', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->recordUrl(
                fn ($record) => FuelLogResource::getUrl('view', ['record' => $record])
            );
    }
}