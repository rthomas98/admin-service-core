<?php

namespace App\Filament\Resources\FuelLogs;

use App\Filament\Resources\FuelLogs\Pages\CreateFuelLog;
use App\Filament\Resources\FuelLogs\Pages\EditFuelLog;
use App\Filament\Resources\FuelLogs\Pages\ListFuelLogs;
use App\Filament\Resources\FuelLogs\Pages\ViewFuelLog;
use App\Filament\Resources\FuelLogs\Schemas\FuelLogForm;
use App\Filament\Resources\FuelLogs\Tables\FuelLogsTable;
use App\Models\FuelLog;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FuelLogResource extends Resource
{
    protected static ?string $model = FuelLog::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-beaker';
    
    protected static ?string $navigationLabel = 'Fuel Logs';
    
    protected static string | UnitEnum | null $navigationGroup = 'Fleet Management';
    
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return FuelLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FuelLogsTable::configure($table);
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
            'index' => ListFuelLogs::route('/'),
            'create' => CreateFuelLog::route('/create'),
            'view' => ViewFuelLog::route('/{record}'),
            'edit' => EditFuelLog::route('/{record}/edit'),
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
        
        // Show only for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();
        
        // Allow creation only for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Allow editing only for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canDelete(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Allow deletion only for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
}