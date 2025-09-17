<?php

namespace App\Filament\Resources\VehicleInspections;

use App\Filament\Resources\VehicleInspections\Pages\CreateVehicleInspection;
use App\Filament\Resources\VehicleInspections\Pages\EditVehicleInspection;
use App\Filament\Resources\VehicleInspections\Pages\ListVehicleInspections;
use App\Filament\Resources\VehicleInspections\Pages\ViewVehicleInspection;
use App\Filament\Resources\VehicleInspections\Schemas\VehicleInspectionForm;
use App\Filament\Resources\VehicleInspections\Tables\VehicleInspectionsTable;
use App\Filament\Traits\FleetManagementResource;
use App\Models\VehicleInspection;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class VehicleInspectionResource extends Resource
{
    use FleetManagementResource;

    protected static ?string $model = VehicleInspection::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Vehicle Inspections';

    protected static ?string $modelLabel = 'Vehicle Inspection';

    protected static string|UnitEnum|null $navigationGroup = 'Fleet Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'inspection_number';

    public static function form(Schema $schema): Schema
    {
        return VehicleInspectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleInspectionsTable::configure($table);
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
            'index' => ListVehicleInspections::route('/'),
            'create' => CreateVehicleInspection::route('/create'),
            'view' => ViewVehicleInspection::route('/{record}'),
            'edit' => EditVehicleInspection::route('/{record}/edit'),
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

        // Only show for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }

    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();

        // Only allow creation for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }

    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();

        // Only allow editing for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        if (! $tenant || ! $tenant->isLivTransport()) {
            return null;
        }

        // Show count of inspections that need attention
        return static::getModel()::query()
            ->where('company_id', $tenant->id)
            ->whereIn('status', ['scheduled', 'failed', 'needs_repair'])
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();

        if ($count > 5) {
            return 'danger';
        } elseif ($count > 0) {
            return 'warning';
        }

        return 'success';
    }
}
