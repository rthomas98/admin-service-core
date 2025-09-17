<?php

namespace App\Filament\Resources\ServiceAreas;

use App\Filament\Resources\ServiceAreas\Pages\CreateServiceArea;
use App\Filament\Resources\ServiceAreas\Pages\EditServiceArea;
use App\Filament\Resources\ServiceAreas\Pages\ListServiceAreas;
use App\Filament\Resources\ServiceAreas\Pages\ViewServiceArea;
use App\Filament\Resources\ServiceAreas\Schemas\ServiceAreaForm;
use App\Filament\Resources\ServiceAreas\Tables\ServiceAreasTable;
use App\Filament\Traits\HasCompanyBasedVisibility;
use App\Models\ServiceArea;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ServiceAreaResource extends Resource
{
    use HasCompanyBasedVisibility;

    protected static ?string $model = ServiceArea::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Service Areas';

    protected static string|UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ServiceAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceAreasTable::configure($table);
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
            'index' => ListServiceAreas::route('/'),
            'create' => CreateServiceArea::route('/create'),
            'view' => ViewServiceArea::route('/{record}'),
            'edit' => EditServiceArea::route('/{record}/edit'),
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
