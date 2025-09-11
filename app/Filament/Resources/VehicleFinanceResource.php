<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleFinances\Pages\CreateVehicleFinance;
use App\Filament\Resources\VehicleFinances\Pages\EditVehicleFinance;
use App\Filament\Resources\VehicleFinances\Pages\ListVehicleFinances;
use App\Filament\Resources\VehicleFinances\Pages\ViewVehicleFinance;
use App\Filament\Resources\VehicleFinances\Schemas\VehicleFinanceForm;
use App\Filament\Resources\VehicleFinances\Tables\VehicleFinancesTable;
use App\Models\VehicleFinance;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class VehicleFinanceResource extends Resource
{
    protected static ?string $model = VehicleFinance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;
    
    protected static string | UnitEnum | null $navigationGroup = 'Financial';
    
    protected static ?string $navigationLabel = 'Vehicle Financing';
    
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return VehicleFinanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleFinancesTable::configure($table);
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
            'index' => ListVehicleFinances::route('/'),
            'create' => CreateVehicleFinance::route('/create'),
            'view' => ViewVehicleFinance::route('/{record}'),
            'edit' => EditVehicleFinance::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $tenant = Filament::getTenant();
        
        if ($tenant) {
            $query->where('company_id', $tenant->id);
        }
        
        return $query;
    }
    
    // Removed tenant restrictions - Financial resources should be visible for all tenants
    // Data filtering is handled by getEloquentQuery() method based on company_id
}
