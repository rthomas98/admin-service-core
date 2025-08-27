<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('quote_number')
                    ->searchable(),
                TextColumn::make('quote_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('valid_until')
                    ->date()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
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
                TextColumn::make('requested_delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('requested_pickup_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('accepted_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('convertedServiceOrder.id')
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
                fn ($record) => QuoteResource::getUrl('view', ['record' => $record])
            );
    }
}
