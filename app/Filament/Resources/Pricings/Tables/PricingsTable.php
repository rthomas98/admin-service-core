<?php

namespace App\Filament\Resources\Pricings\Tables;

use App\Filament\Resources\Pricings\PricingResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('equipment_type')
                    ->searchable(),
                TextColumn::make('size')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('daily_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weekly_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('monthly_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('pickup_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('cleaning_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('maintenance_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('damage_fee')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('late_fee_daily')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('emergency_surcharge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('minimum_rental_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('maximum_rental_days')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('effective_from')
                    ->date()
                    ->sortable(),
                TextColumn::make('effective_until')
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
                fn ($record) => PricingResource::getUrl('view', ['record' => $record])
            );
    }
}
