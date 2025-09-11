<?php

namespace App\Filament\Resources\Pricings;

use App\Filament\Resources\Pricings\Pages\CreatePricing;
use App\Filament\Resources\Pricings\Pages\EditPricing;
use App\Filament\Resources\Pricings\Pages\ListPricings;
use App\Filament\Resources\Pricings\Pages\ViewPricing;
use App\Filament\Resources\Pricings\Schemas\PricingForm;
use App\Filament\Resources\Pricings\Tables\PricingsTable;
use App\Models\Pricing;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PricingResource extends Resource
{
    protected static ?string $model = Pricing::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationLabel = 'Pricing';
    
    protected static string | UnitEnum | null $navigationGroup = 'Financial';
    
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PricingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PricingsTable::configure($table);
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
            'index' => ListPricings::route('/'),
            'create' => CreatePricing::route('/create'),
            'view' => ViewPricing::route('/{record}'),
            'edit' => EditPricing::route('/{record}/edit'),
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
