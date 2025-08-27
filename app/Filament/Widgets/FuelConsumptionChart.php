<?php

namespace App\Filament\Widgets;

use App\Models\FuelLog;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class FuelConsumptionChart extends ChartWidget
{
    protected ?string $heading = 'Fuel Consumption & Costs - Last 30 Days';
    
    protected static ?int $sort = 5;
    
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
        
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(29);
        
        $fuelData = FuelLog::where('company_id', $tenant->id)
            ->whereBetween('fuel_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('DATE(fuel_date) as date')
            ->selectRaw('SUM(gallons) as total_gallons')
            ->selectRaw('SUM(total_cost) as total_cost')
            ->selectRaw('AVG(price_per_gallon) as avg_price')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        $days = collect();
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $dayData = $fuelData->get($dateKey);
            
            $days->push([
                'date' => $date->format('M j'),
                'gallons' => $dayData ? round($dayData->total_gallons, 1) : 0,
                'cost' => $dayData ? round($dayData->total_cost, 2) : 0,
                'price' => $dayData ? round($dayData->avg_price, 2) : null,
            ]);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Gallons',
                    'data' => $days->pluck('gallons')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'yAxisID' => 'y',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Cost ($)',
                    'data' => $days->pluck('cost')->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
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
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Gallons',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Cost ($)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}