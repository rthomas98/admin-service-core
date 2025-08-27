<?php

namespace App\Filament\Resources\ServiceOrders\Tables;

use App\Filament\Resources\ServiceOrders\ServiceOrderResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('service_type')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('delivery_time_start')
                    ->time()
                    ->sortable(),
                TextColumn::make('delivery_time_end')
                    ->time()
                    ->sortable(),
                TextColumn::make('pickup_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('pickup_time_start')
                    ->time()
                    ->sortable(),
                TextColumn::make('pickup_time_end')
                    ->time()
                    ->sortable(),
                TextColumn::make('delivery_address')
                    ->searchable(),
                TextColumn::make('delivery_city')
                    ->searchable(),
                TextColumn::make('delivery_parish')
                    ->searchable(),
                TextColumn::make('delivery_postal_code')
                    ->searchable(),
                TextColumn::make('pickup_address')
                    ->searchable(),
                TextColumn::make('pickup_city')
                    ->searchable(),
                TextColumn::make('pickup_parish')
                    ->searchable(),
                TextColumn::make('pickup_postal_code')
                    ->searchable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('final_amount')
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
                fn ($record) => ServiceOrderResource::getUrl('view', ['record' => $record])
            );
    }
}
