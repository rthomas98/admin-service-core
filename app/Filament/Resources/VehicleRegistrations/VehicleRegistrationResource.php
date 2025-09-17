<?php

namespace App\Filament\Resources\VehicleRegistrations;

use App\Filament\Resources\VehicleRegistrations\Pages\CreateVehicleRegistration;
use App\Filament\Resources\VehicleRegistrations\Pages\EditVehicleRegistration;
use App\Filament\Resources\VehicleRegistrations\Pages\ListVehicleRegistrations;
use App\Filament\Resources\VehicleRegistrations\Schemas\VehicleRegistrationForm;
use App\Filament\Resources\VehicleRegistrations\Tables\VehicleRegistrationsTable;
use App\Filament\Traits\FleetManagementResource;
use App\Models\VehicleRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VehicleRegistrationResource extends Resource
{
    use FleetManagementResource;

    protected static ?string $model = VehicleRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Vehicle Registrations';

    protected static string|UnitEnum|null $navigationGroup = 'Fleet Management';

    protected static ?int $navigationSort = 8;

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
