<?php

namespace App\Filament\Resources\DeliverySchedules;

use App\Filament\Resources\DeliverySchedules\Pages\CreateDeliverySchedule;
use App\Filament\Resources\DeliverySchedules\Pages\EditDeliverySchedule;
use App\Filament\Resources\DeliverySchedules\Pages\ListDeliverySchedules;
use App\Filament\Resources\DeliverySchedules\Pages\ViewDeliverySchedule;
use App\Filament\Resources\DeliverySchedules\Schemas\DeliveryScheduleForm;
use App\Filament\Resources\DeliverySchedules\Tables\DeliverySchedulesTable;
use App\Models\DeliverySchedule;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliveryScheduleResource extends Resource
{
    protected static ?string $model = DeliverySchedule::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'Delivery Schedules';
    
    protected static string | UnitEnum | null $navigationGroup = 'Scheduling';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DeliveryScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliverySchedulesTable::configure($table);
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
            'index' => ListDeliverySchedules::route('/'),
            'create' => CreateDeliverySchedule::route('/create'),
            'view' => ViewDeliverySchedule::route('/{record}'),
            'edit' => EditDeliverySchedule::route('/{record}/edit'),
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
