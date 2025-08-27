<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('invoice.id')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->searchable(),
                TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->searchable(),
                TextColumn::make('transaction_id')
                    ->searchable(),
                TextColumn::make('reference_number')
                    ->searchable(),
                TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('processed_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('gateway')
                    ->searchable(),
                TextColumn::make('gateway_transaction_id')
                    ->searchable(),
                TextColumn::make('fee_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('net_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('check_number')
                    ->searchable(),
                TextColumn::make('check_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('bank_name')
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
                fn ($record) => PaymentResource::getUrl('view', ['record' => $record])
            );
    }
}
