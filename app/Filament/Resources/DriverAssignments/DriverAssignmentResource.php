<?php

namespace App\Filament\Resources\DriverAssignments;

use App\Filament\Resources\DriverAssignments\Pages\CreateDriverAssignment;
use App\Filament\Resources\DriverAssignments\Pages\EditDriverAssignment;
use App\Filament\Resources\DriverAssignments\Pages\ListDriverAssignments;
use App\Filament\Resources\DriverAssignments\Pages\ViewDriverAssignment;
use App\Filament\Resources\DriverAssignments\Schemas\DriverAssignmentForm;
use App\Filament\Resources\DriverAssignments\Tables\DriverAssignmentsTable;
use App\Models\DriverAssignment;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DriverAssignmentResource extends Resource
{
    protected static ?string $model = DriverAssignment::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Driver Assignments';
    
    protected static string | UnitEnum | null $navigationGroup = 'Fleet Management';
    
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DriverAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriverAssignmentsTable::configure($table);
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
            'index' => ListDriverAssignments::route('/'),
            'create' => CreateDriverAssignment::route('/create'),
            'view' => ViewDriverAssignment::route('/{record}'),
            'edit' => EditDriverAssignment::route('/{record}/edit'),
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