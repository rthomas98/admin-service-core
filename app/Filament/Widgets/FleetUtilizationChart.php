<?php

namespace App\Filament\Widgets;

use App\Models\Vehicle;
use App\Models\Trailer;
use App\Models\DriverAssignment;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;

class FleetUtilizationChart extends ChartWidget
{
    protected ?string $heading = 'Fleet Utilization';
    
    protected static ?int $sort = 8;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];
    
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isLivTransport()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        $totalVehicles = Vehicle::where('company_id', $tenant->id)->count();
        $activeVehicles = Vehicle::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->count();
        $inMaintenanceVehicles = Vehicle::where('company_id', $tenant->id)
            ->where('status', 'maintenance')
            ->count();
        $inactiveVehicles = Vehicle::where('company_id', $tenant->id)
            ->where('status', 'inactive')
            ->count();
            
        $totalTrailers = Trailer::where('company_id', $tenant->id)->count();
        $activeTrailers = Trailer::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->count();
        $inMaintenanceTrailers = Trailer::where('company_id', $tenant->id)
            ->where('status', 'maintenance')
            ->count();
        $inactiveTrailers = Trailer::where('company_id', $tenant->id)
            ->where('status', 'inactive')
            ->count();
            
        $vehiclesInUse = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->distinct('vehicle_id')
            ->count('vehicle_id');
            
        $trailersInUse = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->whereNotNull('trailer_id')
            ->distinct('trailer_id')
            ->count('trailer_id');
        
        return [
            'datasets' => [
                [
                    'label' => 'Fleet Status',
                    'data' => [
                        $vehiclesInUse,
                        $activeVehicles - $vehiclesInUse,
                        $inMaintenanceVehicles,
                        $inactiveVehicles,
                        $trailersInUse,
                        $activeTrailers - $trailersInUse,
                        $inMaintenanceTrailers,
                        $inactiveTrailers,
                    ],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // In Use - Green
                        'rgba(59, 130, 246, 0.8)',  // Available - Blue
                        'rgba(251, 146, 60, 0.8)',  // Maintenance - Orange
                        'rgba(239, 68, 68, 0.8)',   // Inactive - Red
                        'rgba(34, 197, 94, 0.6)',  // Trailers In Use - Light Green
                        'rgba(59, 130, 246, 0.6)',  // Trailers Available - Light Blue
                        'rgba(251, 146, 60, 0.6)',  // Trailers Maintenance - Light Orange
                        'rgba(239, 68, 68, 0.6)',   // Trailers Inactive - Light Red
                    ],
                ],
            ],
            'labels' => [
                'Vehicles In Use',
                'Vehicles Available',
                'Vehicles In Maintenance',
                'Vehicles Inactive',
                'Trailers In Use',
                'Trailers Available',
                'Trailers In Maintenance',
                'Trailers Inactive',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 10,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "
                            function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed;
                                return label;
                            }
                        ",
                    ],
                ],
            ],
        ];
    }
}