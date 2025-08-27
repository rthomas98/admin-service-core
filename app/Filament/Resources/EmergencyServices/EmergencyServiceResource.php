<?php

namespace App\Filament\Resources\EmergencyServices;

use App\Filament\Resources\EmergencyServices\Pages\CreateEmergencyService;
use App\Filament\Resources\EmergencyServices\Pages\EditEmergencyService;
use App\Filament\Resources\EmergencyServices\Pages\ListEmergencyServices;
use App\Filament\Resources\EmergencyServices\Pages\ViewEmergencyService;
use App\Filament\Resources\EmergencyServices\Schemas\EmergencyServiceForm;
use App\Filament\Resources\EmergencyServices\Tables\EmergencyServicesTable;
use App\Models\EmergencyService;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmergencyServiceResource extends Resource
{
    protected static ?string $model = EmergencyService::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-exclamation-triangle';
    
    protected static ?string $navigationLabel = 'Emergency Services';
    
    protected static string | UnitEnum | null $navigationGroup = 'Operations';
    
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return EmergencyServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmergencyServicesTable::configure($table);
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
            'index' => ListEmergencyServices::route('/'),
            'create' => CreateEmergencyService::route('/create'),
            'view' => ViewEmergencyService::route('/{record}'),
            'edit' => EditEmergencyService::route('/{record}/edit'),
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
