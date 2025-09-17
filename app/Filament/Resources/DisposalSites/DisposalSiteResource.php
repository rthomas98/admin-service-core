<?php

namespace App\Filament\Resources\DisposalSites;

use App\Filament\Resources\DisposalSites\Pages\CreateDisposalSite;
use App\Filament\Resources\DisposalSites\Pages\EditDisposalSite;
use App\Filament\Resources\DisposalSites\Pages\ListDisposalSites;
use App\Filament\Resources\DisposalSites\Pages\ViewDisposalSite;
use App\Filament\Resources\DisposalSites\Schemas\DisposalSiteForm;
use App\Filament\Resources\DisposalSites\Tables\DisposalSitesTable;
use App\Filament\Traits\HasCompanyBasedVisibility;
use App\Models\DisposalSite;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class DisposalSiteResource extends Resource
{
    use HasCompanyBasedVisibility;

    protected static ?string $model = DisposalSite::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Disposal Sites';

    protected static string|UnitEnum|null $navigationGroup = 'Waste Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Disposal Site';

    protected static ?string $pluralModelLabel = 'Disposal Sites';

    public static function form(Schema $schema): Schema
    {
        return DisposalSiteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DisposalSitesTable::configure($table);
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
            'index' => ListDisposalSites::route('/'),
            'create' => CreateDisposalSite::route('/create'),
            'view' => ViewDisposalSite::route('/{record}'),
            'edit' => EditDisposalSite::route('/{record}/edit'),
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

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'location', 'parish', 'site_type'];
    }

    // Only visible for RAW Disposal company
    public static function shouldRegisterNavigation(): bool
    {
        $tenant = Filament::getTenant();

        return $tenant && $tenant->isRawDisposal();
    }
}
