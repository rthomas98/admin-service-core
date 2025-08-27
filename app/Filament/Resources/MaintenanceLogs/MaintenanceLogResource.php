<?php

namespace App\Filament\Resources\MaintenanceLogs;

use App\Filament\Resources\MaintenanceLogs\Pages\CreateMaintenanceLog;
use App\Filament\Resources\MaintenanceLogs\Pages\EditMaintenanceLog;
use App\Filament\Resources\MaintenanceLogs\Pages\ListMaintenanceLogs;
use App\Filament\Resources\MaintenanceLogs\Pages\ViewMaintenanceLog;
use App\Filament\Resources\MaintenanceLogs\Schemas\MaintenanceLogForm;
use App\Filament\Resources\MaintenanceLogs\Tables\MaintenanceLogsTable;
use App\Models\MaintenanceLog;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLogResource extends Resource
{
    protected static ?string $model = MaintenanceLog::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationLabel = 'Maintenance Logs';
    
    protected static string | UnitEnum | null $navigationGroup = 'Operations';
    
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return MaintenanceLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaintenanceLogsTable::configure($table);
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
            'index' => ListMaintenanceLogs::route('/'),
            'create' => CreateMaintenanceLog::route('/create'),
            'view' => ViewMaintenanceLog::route('/{record}'),
            'edit' => EditMaintenanceLog::route('/{record}/edit'),
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
        
        // Only show for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
    
    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow creation for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
    
    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow editing for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
    
    public static function canDelete(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow deletion for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
}
