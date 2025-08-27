<?php

namespace App\Filament\Widgets;

use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\FuelLog;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DriverPerformanceChart extends ChartWidget
{
    protected ?string $heading = '🏆 Top Driver Performance - This Month';
    
    protected static ?int $sort = 6;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];
    
    protected ?string $maxHeight = '350px';
    
    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isLivTransport()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        // Get top 10 drivers by completed assignments
        $topDrivers = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereBetween('end_date', [$startDate, $endDate])
            ->select('driver_id', DB::raw('COUNT(*) as completed_trips'))
            ->selectRaw('SUM(mileage_end - mileage_start) as total_miles')
            ->selectRaw('AVG(mileage_end - mileage_start) as avg_miles_per_trip')
            ->selectRaw('SUM(cargo_weight) as total_cargo')
            ->groupBy('driver_id')
            ->orderBy('completed_trips', 'desc')
            ->limit(10)
            ->with('driver')
            ->get();
            
        // Get fuel costs for each driver
        $driverFuelData = [];
        foreach ($topDrivers as $driverData) {
            if ($driverData->driver) {
                // Calculate average fuel cost per trip instead of MPG
                $totalFuelCost = FuelLog::where('company_id', $tenant->id)
                    ->where('driver_id', $driverData->driver_id)
                    ->whereBetween('fuel_date', [$startDate, $endDate])
                    ->sum('total_cost');
                    
                $avgFuelCostPerTrip = $driverData->completed_trips > 0 
                    ? round($totalFuelCost / $driverData->completed_trips, 2)
                    : 0;
                    
                $driverFuelData[$driverData->driver_id] = $avgFuelCostPerTrip;
            }
        }
        
        $labels = $topDrivers->map(fn ($d) => $d->driver ? explode(' ', $d->driver->name)[0] : 'Unknown')->toArray();
        $completedTrips = $topDrivers->pluck('completed_trips')->toArray();
        $totalMiles = $topDrivers->map(fn ($d) => round($d->total_miles / 100))->toArray(); // Scaled down for visibility
        $avgFuelCosts = $topDrivers->map(fn ($d) => $driverFuelData[$d->driver_id] ?? 0)->toArray();
        
        return [
            'datasets' => [
                [
                    'label' => 'Completed Trips',
                    'data' => $completedTrips,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Miles (×100)',
                    'data' => $totalMiles,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Avg Fuel Cost/Trip ($)',
                    'data' => $avgFuelCosts,
                    'type' => 'line',
                    'fill' => false,
                    'borderColor' => 'rgb(251, 146, 60)',
                    'backgroundColor' => 'rgba(251, 146, 60, 0.1)',
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'usePointStyle' => true,
                        'padding' => 15,
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleFont' => [
                        'size' => 14,
                    ],
                    'bodyFont' => [
                        'size' => 13,
                    ],
                    'padding' => 12,
                    'displayColors' => true,
                    'callbacks' => [
                        'label' => "
                            function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.datasetIndex === 1) {
                                    label += (context.parsed.y * 100).toLocaleString() + ' miles';
                                } else if (context.datasetIndex === 2) {
                                    label += '$' + context.parsed.y.toFixed(2);
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        ",
                    ],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Trips / Miles (×100)',
                    ],
                    'beginAtZero' => true,
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Avg Fuel Cost per Trip ($)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}