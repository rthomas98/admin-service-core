<?php

namespace App\Filament\Resources\DriverAssignments\Tables;

use App\Filament\Resources\DriverAssignments\DriverAssignmentResource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DriverAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('driver.full_name')
                    ->label('Driver')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('vehicle.full_description')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('trailer.full_description')
                    ->label('Trailer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'active' => 'success',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                    }),
                
                TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                
                TextColumn::make('end_date')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('route')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('origin')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('destination')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('total_mileage')
                    ->label('Miles')
                    ->numeric()
                    ->suffix(' mi')
                    ->toggleable(),
                
                TextColumn::make('fuel_used')
                    ->numeric()
                    ->suffix(' gal')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                
                SelectFilter::make('driver_id')
                    ->relationship('driver', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('vehicle_id')
                    ->relationship('vehicle', 'vin')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->year} {$record->make} {$record->model}")
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
                fn ($record) => DriverAssignmentResource::getUrl('view', ['record' => $record])
            );
    }
}