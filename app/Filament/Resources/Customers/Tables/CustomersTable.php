<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_number')
                    ->label('Customer #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name', 'name', 'organization'])
                    ->sortable('name'),
                TextColumn::make('emails')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->display_phone
                    ),
                TextColumn::make('city')
                    ->label('City')
                    ->searchable(),
                TextColumn::make('state')
                    ->label('State')
                    ->searchable(),
                TextColumn::make('business_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Residential' => 'info',
                        'Commercial' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('customer_since')
                    ->label('Since')
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
            ->defaultSort('customer_since', 'desc')
            ->filters([
                SelectFilter::make('business_type')
                    ->label('Business Type')
                    ->options([
                        'Residential' => 'Residential',
                        'Commercial' => 'Commercial',
                    ]),
                SelectFilter::make('state')
                    ->label('State')
                    ->options([
                        'LA' => 'Louisiana',
                        'MS' => 'Mississippi', 
                        'AL' => 'Alabama',
                        'TX' => 'Texas',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->recordUrl(
                fn ($record) => CustomerResource::getUrl('view', ['record' => $record])
            )
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
