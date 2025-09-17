<?php

namespace App\Filament\Resources\ServiceSchedules;

use App\Filament\Resources\ServiceSchedules\Pages\CreateServiceSchedule;
use App\Filament\Resources\ServiceSchedules\Pages\EditServiceSchedule;
use App\Filament\Resources\ServiceSchedules\Pages\ListServiceSchedules;
use App\Filament\Resources\ServiceSchedules\Pages\ViewServiceSchedule;
use App\Filament\Resources\ServiceSchedules\Schemas\ServiceScheduleForm;
use App\Filament\Resources\ServiceSchedules\Tables\ServiceSchedulesTable;
use App\Filament\Traits\HasCompanyBasedVisibility;
use App\Models\ServiceSchedule;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ServiceScheduleResource extends Resource
{
    use HasCompanyBasedVisibility;

    protected static ?string $model = ServiceSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench';

    protected static ?string $navigationLabel = 'Service Schedules';

    protected static string|UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ServiceScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceSchedulesTable::configure($table);
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
            'index' => ListServiceSchedules::route('/'),
            'create' => CreateServiceSchedule::route('/create'),
            'view' => ViewServiceSchedule::route('/{record}'),
            'edit' => EditServiceSchedule::route('/{record}/edit'),
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
