<?php

namespace App\Filament\Widgets;

use App\Models\WasteCollection;
use App\Models\WasteRoute;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CollectionScheduleWidget extends TableWidget
{
    protected static ?string $heading = '📅 Today\'s Collection Schedule';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isRawDisposal()) {
            return WasteCollection::query()->whereRaw('1 = 0');
        }
        
        $today = Carbon::today();
        
        return WasteCollection::where('company_id', $tenant->id)
            ->whereDate('scheduled_date', $today)
            ->with(['customer', 'route', 'driver', 'truck'])
            ->orderBy('scheduled_time', 'asc');
    }
    
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('scheduled_time')
                ->label('Time')
                ->time('H:i')
                ->weight('bold')
                ->color(fn ($record) => 
                    Carbon::parse($record->scheduled_date . ' ' . $record->scheduled_time)->isPast() 
                        ? 'gray' 
                        : 'primary'
                ),
                
            TextColumn::make('route.name')
                ->label('Route')
                ->badge()
                ->color('info')
                ->searchable(),
                
            TextColumn::make('customer.name')
                ->label('Customer')
                ->searchable()
                ->limit(25)
                ->tooltip(fn ($record) => $record->customer->name ?? 'N/A'),
                
            TextColumn::make('customer.address')
                ->label('Location')
                ->description(fn ($record) => $record->customer ? $record->customer->city : '')
                ->icon('heroicon-o-map-pin')
                ->iconColor('gray')
                ->limit(30),
                
            BadgeColumn::make('waste_type')
                ->label('Type')
                ->colors([
                    'primary' => 'general',
                    'success' => 'recyclable',
                    'warning' => 'organic',
                    'danger' => 'hazardous',
                    'info' => 'construction',
                ])
                ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                
            TextColumn::make('estimated_weight')
                ->label('Est. Weight')
                ->suffix(' tons')
                ->alignEnd()
                ->color('gray'),
                
            TextColumn::make('driver.name')
                ->label('Driver')
                ->placeholder('Unassigned')
                ->icon('heroicon-o-user')
                ->iconColor(fn ($record) => $record->driver ? 'success' : 'gray'),
                
            TextColumn::make('truck.unit_number')
                ->label('Truck')
                ->placeholder('Unassigned')
                ->icon('heroicon-o-truck')
                ->iconColor(fn ($record) => $record->truck ? 'success' : 'gray'),
                
            BadgeColumn::make('status')
                ->colors([
                    'gray' => 'scheduled',
                    'warning' => 'in_progress',
                    'success' => 'completed',
                    'danger' => 'missed',
                    'info' => 'rescheduled',
                ])
                ->icons([
                    'heroicon-o-clock' => 'scheduled',
                    'heroicon-o-truck' => 'in_progress',
                    'heroicon-o-check-circle' => 'completed',
                    'heroicon-o-x-circle' => 'missed',
                    'heroicon-o-arrow-path' => 'rescheduled',
                ]),
                
            TextColumn::make('completion_percentage')
                ->label('Progress')
                ->getStateUsing(function () {
                    $tenant = Filament::getTenant();
                    if (!$tenant) return '0%';
                    
                    $today = Carbon::today();
                    $total = WasteCollection::where('company_id', $tenant->id)
                        ->whereDate('scheduled_date', $today)
                        ->count();
                    $completed = WasteCollection::where('company_id', $tenant->id)
                        ->whereDate('scheduled_date', $today)
                        ->where('status', 'completed')
                        ->count();
                    
                    if ($total === 0) return '0%';
                    return round(($completed / $total) * 100) . '%';
                })
                ->color(fn ($state) => 
                    intval($state) >= 80 ? 'success' : 
                    (intval($state) >= 50 ? 'warning' : 'danger')
                )
                ->weight('bold')
                ->alignEnd(),
        ];
    }
    
    protected function getTablePaginationPageOptions(): array
    {
        return [10, 25];
    }
    
    protected function getTableEmptyStateHeading(): ?string
    {
        return '📋 No Collections Scheduled';
    }
    
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'There are no waste collections scheduled for today.';
    }
    
    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-calendar-days';
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([])
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->striped();
    }
}