<?php

namespace App\Filament\Resources\WasteRoutes;

use App\Filament\Resources\WasteRoutes\Pages\CreateWasteRoute;
use App\Filament\Resources\WasteRoutes\Pages\EditWasteRoute;
use App\Filament\Resources\WasteRoutes\Pages\ListWasteRoutes;
use App\Filament\Resources\WasteRoutes\Pages\ViewWasteRoute;
use App\Filament\Resources\WasteRoutes\Schemas\WasteRouteForm;
use App\Filament\Resources\WasteRoutes\Tables\WasteRoutesTable;
use App\Filament\Traits\HasCompanyBasedVisibility;
use App\Models\WasteRoute;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class WasteRouteResource extends Resource
{
    use HasCompanyBasedVisibility;

    protected static ?string $model = WasteRoute::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Waste Routes';

    protected static string|UnitEnum|null $navigationGroup = 'Waste Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'route_name';

    protected static ?string $modelLabel = 'Waste Route';

    protected static ?string $pluralModelLabel = 'Waste Routes';

    public static function form(Schema $schema): Schema
    {
        return WasteRouteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WasteRoutesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // Add relations if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWasteRoutes::route('/'),
            'create' => CreateWasteRoute::route('/create'),
            'view' => ViewWasteRoute::route('/{record}'),
            'edit' => EditWasteRoute::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $tenant = Filament::getTenant();

        if ($tenant) {
            $query->where('company_id', $tenant->id);
        }

        return $query->with(['driver', 'vehicle']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['route_name', 'route_code', 'driver.name'];
    }

    // Only visible for RAW Disposal company
    public static function shouldRegisterNavigation(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant && $tenant->isRawDisposal();
    }
}
