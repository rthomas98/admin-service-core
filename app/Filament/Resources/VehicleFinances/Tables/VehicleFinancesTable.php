<?php

namespace App\Filament\Resources\VehicleFinances\Tables;

use App\Filament\Resources\VehicleFinanceResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class VehicleFinancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_number')
                    ->searchable()
                    ->sortable()
                    ->label('Account #'),
                TextColumn::make('financeable_type')
                    ->label('Asset Type')
                    ->getStateUsing(function ($record) {
                        return class_basename($record->financeable_type);
                    }),
                TextColumn::make('financeable.unit_number')
                    ->label('Asset')
                    ->searchable(),
                TextColumn::make('financeCompany.name')
                    ->label('Finance Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('finance_type')
                    ->badge()
                    ->colors([
                        'primary' => 'lease',
                        'success' => 'loan',
                        'warning' => 'rental',
                    ]),
                TextColumn::make('monthly_payment')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
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
                fn ($record) => VehicleFinanceResource::getUrl('view', ['record' => $record])
            );
    }
}
