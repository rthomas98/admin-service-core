<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\Trailer;
use App\Models\Driver;
use App\Models\DriverAssignment;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class FleetOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    protected int | string | array $columnSpan = 'full';
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isLivTransport()) {
            return [];
        }
        
        $vehicleCount = Vehicle::where('company_id', $tenant->id)->count();
        $activeVehicles = Vehicle::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->count();
        
        $trailerCount = Trailer::where('company_id', $tenant->id)->count();
        $activeTrailers = Trailer::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->count();
        
        $driverCount = Driver::where('company_id', $tenant->id)->count();
        $activeDrivers = Driver::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->count();
        
        $activeAssignments = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->count();
        
        $completedToday = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereDate('end_date', today())
            ->count();
        
        return [
            Stat::make('Total Fleet', $vehicleCount . ' Vehicles')
                ->description($activeVehicles . ' active, ' . ($vehicleCount - $activeVehicles) . ' inactive')
                ->icon('heroicon-o-truck')
                ->chart([7, 3, 4, 5, 6, 8, 10])
                ->color('primary'),
                
            Stat::make('Trailers', $trailerCount . ' Units')
                ->description($activeTrailers . ' active, ' . ($trailerCount - $activeTrailers) . ' inactive')
                ->icon('heroicon-o-cube')
                ->color('info'),
                
            Stat::make('Drivers', $driverCount . ' Total')
                ->description($activeDrivers . ' active today')
                ->icon('heroicon-o-users')
                ->chart([12, 10, 14, 16, 14, 16, 18])
                ->color('success'),
                
            Stat::make('Active Routes', $activeAssignments)
                ->description($completedToday . ' completed today')
                ->icon('heroicon-o-map')
                ->color('warning'),
        ];
    }
}
