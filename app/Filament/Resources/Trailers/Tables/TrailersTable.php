<?php

namespace App\Filament\Resources\Trailers\Tables;

use App\Filament\Resources\Trailers\TrailerResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrailersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_number')
                    ->searchable(),
                TextColumn::make('vin')
                    ->searchable(),
                TextColumn::make('year')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('make')
                    ->searchable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('type')
                    ->searchable(),
                TextColumn::make('length')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('width')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('height')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('capacity_weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('capacity_volume')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('license_plate')
                    ->searchable(),
                TextColumn::make('registration_state')
                    ->searchable(),
                TextColumn::make('registration_expiry')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('purchase_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_vendor')
                    ->searchable(),
                IconColumn::make('is_leased')
                    ->boolean(),
                TextColumn::make('lease_end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('last_inspection_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('next_inspection_date')
                    ->date()
                    ->sortable(),
                ImageColumn::make('image_path'),
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
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn ($record) => TrailerResource::getUrl('view', ['record' => $record])
            );
    }
}
