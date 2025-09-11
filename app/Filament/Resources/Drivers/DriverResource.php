<?php

namespace App\Filament\Resources\Drivers;

use App\Filament\Resources\Drivers\Pages\CreateDriver;
use App\Filament\Resources\Drivers\Pages\EditDriver;
use App\Filament\Resources\Drivers\Pages\ListDrivers;
use App\Filament\Resources\Drivers\Pages\ViewDriver;
use App\Filament\Resources\Drivers\Schemas\DriverForm;
use App\Filament\Resources\Drivers\Tables\DriversTable;
use App\Models\Driver;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DriverResource extends Resource
{
    protected static ?string $model = Driver::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Drivers';
    
    protected static string | UnitEnum | null $navigationGroup = 'Fleet Management';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DriverForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DriversTable::configure($table);
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
            'index' => ListDrivers::route('/'),
            'create' => CreateDriver::route('/create'),
            'view' => ViewDriver::route('/{record}'),
            'edit' => EditDriver::route('/{record}/edit'),
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
        
        // Show for both RAW Disposal and LIV Transport companies
        return $tenant && ($tenant->isRawDisposal() || $tenant->isLivTransport());
    }
    
    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();
        
        // Allow creation for both RAW Disposal and LIV Transport companies
        return $tenant && ($tenant->isRawDisposal() || $tenant->isLivTransport());
    }
    
    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Allow editing for both RAW Disposal and LIV Transport companies
        return $tenant && ($tenant->isRawDisposal() || $tenant->isLivTransport());
    }
    
    public static function canDelete(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Allow deletion for both RAW Disposal and LIV Transport companies
        return $tenant && ($tenant->isRawDisposal() || $tenant->isLivTransport());
    }
}
