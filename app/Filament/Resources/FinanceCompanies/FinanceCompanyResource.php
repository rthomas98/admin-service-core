<?php

namespace App\Filament\Resources\FinanceCompanies;

use App\Filament\Resources\FinanceCompanies\Pages\CreateFinanceCompany;
use App\Filament\Resources\FinanceCompanies\Pages\EditFinanceCompany;
use App\Filament\Resources\FinanceCompanies\Pages\ListFinanceCompanies;
use App\Filament\Resources\FinanceCompanies\Pages\ViewFinanceCompany;
use App\Filament\Resources\FinanceCompanies\Schemas\FinanceCompanyForm;
use App\Filament\Resources\FinanceCompanies\Tables\FinanceCompaniesTable;
use App\Models\FinanceCompany;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class FinanceCompanyResource extends Resource
{
    protected static ?string $model = FinanceCompany::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;
    
    protected static string | UnitEnum | null $navigationGroup = 'Financial';
    
    protected static ?string $navigationLabel = 'Finance Companies';
    
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return FinanceCompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FinanceCompaniesTable::configure($table);
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
            'index' => ListFinanceCompanies::route('/'),
            'create' => CreateFinanceCompany::route('/create'),
            'view' => ViewFinanceCompany::route('/{record}'),
            'edit' => EditFinanceCompany::route('/{record}/edit'),
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
