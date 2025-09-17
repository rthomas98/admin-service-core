<?php

namespace App\Filament\Resources\WasteCollections;

use App\Filament\Resources\WasteCollections\Pages\CreateWasteCollection;
use App\Filament\Resources\WasteCollections\Pages\EditWasteCollection;
use App\Filament\Resources\WasteCollections\Pages\ListWasteCollections;
use App\Filament\Resources\WasteCollections\Pages\ViewWasteCollection;
use App\Filament\Resources\WasteCollections\Schemas\WasteCollectionForm;
use App\Filament\Resources\WasteCollections\Tables\WasteCollectionsTable;
use App\Filament\Traits\HasCompanyBasedVisibility;
use App\Models\WasteCollection;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class WasteCollectionResource extends Resource
{
    use HasCompanyBasedVisibility;

    protected static ?string $model = WasteCollection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Waste Collections';

    protected static string|UnitEnum|null $navigationGroup = 'Waste Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'collection_number';

    protected static ?string $modelLabel = 'Waste Collection';

    protected static ?string $pluralModelLabel = 'Waste Collections';

    public static function form(Schema $schema): Schema
    {
        return WasteCollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WasteCollectionsTable::configure($table);
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
            'index' => ListWasteCollections::route('/'),
            'create' => CreateWasteCollection::route('/create'),
            'view' => ViewWasteCollection::route('/{record}'),
            'edit' => EditWasteCollection::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $tenant = Filament::getTenant();

        if ($tenant) {
            $query->where('company_id', $tenant->id);
        }

        return $query->with(['customer', 'wasteRoute', 'disposalSite', 'driver', 'vehicle']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['collection_number', 'customer.name', 'driver.name'];
    }

    // Only visible for RAW Disposal company
    public static function shouldRegisterNavigation(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant && $tenant->isRawDisposal();
    }
}
