<?php

namespace App\Filament\Resources\VehicleMaintenances;

use App\Filament\Resources\VehicleMaintenances\Pages\CreateVehicleMaintenance;
use App\Filament\Resources\VehicleMaintenances\Pages\EditVehicleMaintenance;
use App\Filament\Resources\VehicleMaintenances\Pages\ListVehicleMaintenances;
use App\Filament\Resources\VehicleMaintenances\Schemas\VehicleMaintenanceForm;
use App\Filament\Resources\VehicleMaintenances\Tables\VehicleMaintenancesTable;
use App\Models\VehicleMaintenance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceResource extends Resource
{
    protected static ?string $model = VehicleMaintenance::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Vehicle Maintenance';
    
    protected static ?string $modelLabel = 'Maintenance Record';
    
    protected static string | \UnitEnum | null $navigationGroup = 'Fleet Management';
    
    protected static ?int $navigationSort = 2;
    
    protected static ?string $recordTitleAttribute = 'maintenance_number';

    public static function form(Schema $schema): Schema
    {
        return VehicleMaintenanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleMaintenancesTable::configure($table);
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
            'index' => ListVehicleMaintenances::route('/'),
            'create' => CreateVehicleMaintenance::route('/create'),
            'view' => \App\Filament\Resources\VehicleMaintenances\Pages\ViewVehicleMaintenance::route('/{record}'),
            'edit' => EditVehicleMaintenance::route('/{record}/edit'),
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
    
    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();
        
        // Only show for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow creation for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow editing for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isLivTransport()) {
            return null;
        }
        
        // Show count of maintenance that needs attention
        return static::getModel()::query()
            ->where('company_id', $tenant->id)
            ->whereIn('status', ['scheduled', 'in_progress', 'overdue'])
            ->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();
        
        if ($count > 10) {
            return 'danger';
        } elseif ($count > 5) {
            return 'warning';
        }
        
        return 'success';
    }
}
