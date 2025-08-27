<?php

namespace App\Filament\Widgets;

use App\Models\DriverAssignment;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentDriverAssignments extends TableWidget
{
    protected static ?string $heading = 'Recent Driver Assignments';
    
    protected static ?int $sort = 7;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isLivTransport()) {
            return DriverAssignment::query()->whereRaw('1 = 0');
        }
        
        return DriverAssignment::where('company_id', $tenant->id)
            ->with(['driver', 'vehicle'])
            ->latest('start_date')
            ->limit(10);
    }
    
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('driver.name')
                ->label('Driver')
                ->searchable()
                ->sortable()
                ->icon('heroicon-o-user')
                ->iconColor('primary'),
                
            TextColumn::make('vehicle.unit_number')
                ->label('Vehicle')
                ->searchable()
                ->sortable()
                ->icon('heroicon-o-truck')
                ->iconColor('info'),
                
            TextColumn::make('route')
                ->label('Route')
                ->limit(30)
                ->tooltip(fn ($record) => $record->route),
                
            TextColumn::make('cargo_type')
                ->label('Cargo')
                ->badge()
                ->colors([
                    'primary' => 'General Freight',
                    'success' => 'Equipment',
                    'warning' => 'Construction Materials',
                    'danger' => 'Machinery',
                    'info' => 'default',
                ]),
                
            BadgeColumn::make('status')
                ->colors([
                    'success' => 'completed',
                    'warning' => 'scheduled',
                    'primary' => 'active',
                    'danger' => 'cancelled',
                ])
                ->icons([
                    'heroicon-o-check-circle' => 'completed',
                    'heroicon-o-clock' => 'scheduled',
                    'heroicon-o-truck' => 'active',
                    'heroicon-o-x-circle' => 'cancelled',
                ]),
                
            TextColumn::make('start_date')
                ->label('Start')
                ->date('M j')
                ->sortable(),
                
            TextColumn::make('expected_duration_hours')
                ->label('Duration')
                ->suffix(' hrs')
                ->alignEnd(),
                
            TextColumn::make('mileage_start')
                ->label('Miles')
                ->getStateUsing(fn ($record) => 
                    $record->mileage_end 
                        ? number_format($record->mileage_end - $record->mileage_start) 
                        : '-'
                )
                ->alignEnd(),
        ];
    }
    
    protected function getTableFilters(): array
    {
        return [];
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
        return 'start_date';
    }
    
    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
    
    protected function getTablePaginationPageOptions(): array
    {
        return [5, 10];
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->actions($this->getTableActions())
            ->bulkActions($this->getTableBulkActions())
            ->defaultSort($this->getDefaultTableSortColumn(), $this->getDefaultTableSortDirection())
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}