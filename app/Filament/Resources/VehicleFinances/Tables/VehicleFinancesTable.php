<?php

namespace App\Filament\Resources\VehicleFinances\Tables;

use App\Filament\Resources\VehicleFinances\VehicleFinanceResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class VehicleFinancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                //
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
