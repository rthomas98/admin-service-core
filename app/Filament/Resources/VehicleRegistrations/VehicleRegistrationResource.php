<?php

namespace App\Filament\Resources\VehicleRegistrations;

use App\Filament\Resources\VehicleRegistrations\Pages\CreateVehicleRegistration;
use App\Filament\Resources\VehicleRegistrations\Pages\EditVehicleRegistration;
use App\Filament\Resources\VehicleRegistrations\Pages\ListVehicleRegistrations;
use App\Filament\Resources\VehicleRegistrations\Schemas\VehicleRegistrationForm;
use App\Filament\Resources\VehicleRegistrations\Tables\VehicleRegistrationsTable;
use App\Models\VehicleRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleRegistrationResource extends Resource
{
    protected static ?string $model = VehicleRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return VehicleRegistrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleRegistrationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleRegistrations::route('/'),
            'create' => CreateVehicleRegistration::route('/create'),
            'edit' => EditVehicleRegistration::route('/{record}/edit'),
        ];
    }
}
