<?php

namespace App\Filament\Widgets;

use App\Models\EmergencyService;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class EmergencyServicesAlert extends TableWidget
{
    protected static ?string $heading = '🚨 Emergency Services - Active Requests';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];
    
    
    protected function getTableQuery(): Builder
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isLivTransport()) {
            return EmergencyService::query()->whereRaw('1 = 0');
        }
        
        return EmergencyService::where('company_id', $tenant->id)
            ->whereIn('status', ['pending', 'assigned', 'dispatched', 'on_site'])
            ->with(['customer', 'assignedDriver', 'assignedTechnician'])
            ->orderByRaw("CASE 
                WHEN urgency_level = 'critical' THEN 1
                WHEN urgency_level = 'high' THEN 2
                WHEN urgency_level = 'medium' THEN 3
                ELSE 4
            END")
            ->orderBy('request_datetime', 'asc');
    }
    
    protected function getTableColumns(): array
    {
        return [
            BadgeColumn::make('urgency_level')
                ->label('Priority')
                ->colors([
                    'danger' => 'critical',
                    'warning' => 'high',
                    'primary' => 'medium',
                    'gray' => 'low',
                ])
                ->icons([
                    'heroicon-o-exclamation-triangle' => 'critical',
                    'heroicon-o-exclamation-circle' => 'high',
                    'heroicon-o-information-circle' => 'medium',
                    'heroicon-o-check-circle' => 'low',
                ])
                ->extraAttributes(fn ($record) => [
                    'class' => $record->urgency_level === 'critical' ? 'animate-pulse' : '',
                ]),
                
            TextColumn::make('emergency_number')
                ->label('Emergency #')
                ->searchable()
                ->copyable()
                ->copyMessage('Emergency number copied')
                ->weight('bold'),
                
            TextColumn::make('emergency_type')
                ->label('Type')
                ->badge()
                ->colors([
                    'primary' => 'delivery',
                    'success' => 'pickup',
                    'warning' => 'repair',
                    'danger' => 'replacement',
                    'info' => 'cleaning',
                ]),
                
            TextColumn::make('customer.name')
                ->label('Customer')
                ->searchable()
                ->limit(20)
                ->tooltip(fn ($record) => $record->customer->name ?? 'N/A'),
                
            TextColumn::make('location_city')
                ->label('Location')
                ->description(fn ($record) => $record->location_parish)
                ->icon('heroicon-o-map-pin')
                ->iconColor('danger'),
                
            TextColumn::make('request_datetime')
                ->label('Response Time')
                ->getStateUsing(function ($record) {
                    $minutes = Carbon::parse($record->request_datetime)->diffInMinutes(now());
                    if ($minutes < 60) {
                        return $minutes . ' min';
                    }
                    return round($minutes / 60, 1) . ' hrs';
                })
                ->description(fn ($record) => 'Target: ' . $record->target_response_minutes . ' min')
                ->color(fn ($record) => 
                    Carbon::parse($record->request_datetime)->diffInMinutes(now()) > $record->target_response_minutes
                        ? 'danger'
                        : 'success'
                ),
                
            BadgeColumn::make('status')
                ->colors([
                    'gray' => 'pending',
                    'warning' => 'assigned',
                    'primary' => 'dispatched',
                    'success' => 'on_site',
                ])
                ->icons([
                    'heroicon-o-clock' => 'pending',
                    'heroicon-o-user-circle' => 'assigned',
                    'heroicon-o-truck' => 'dispatched',
                    'heroicon-o-wrench' => 'on_site',
                ]),
                
            TextColumn::make('assignedDriver.name')
                ->label('Driver')
                ->placeholder('Unassigned')
                ->icon('heroicon-o-user')
                ->iconColor(fn ($record) => $record->assignedDriver ? 'success' : 'gray'),
        ];
    }
    
    protected function getTableActions(): array
    {
        return [];
    }
    
    protected function getTableBulkActions(): array
    {
        return [];
    }
    
    protected function getDefaultTableSortColumn(): ?string
    {
        return null; // We're using custom ordering
    }
    
    protected function getTablePaginationPageOptions(): array
    {
        return [5];
    }
    
    protected function getTableEmptyStateHeading(): ?string
    {
        return '✅ No Active Emergencies';
    }
    
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'All emergency services have been handled.';
    }
    
    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-shield-check';
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([])
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->striped()
;
    }
}