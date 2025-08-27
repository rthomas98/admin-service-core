<?php

namespace App\Filament\Widgets;

use App\Models\WasteCollection;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WasteVolumeChart extends ChartWidget
{
    protected ?string $heading = '📊 Waste Collection Trends - Last 30 Days';
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];
    
    protected ?string $maxHeight = '350px';
    
    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isRawDisposal()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }
        
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(29);
        
        // Get waste collection data grouped by type
        $collections = WasteCollection::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('DATE(completed_at) as date')
            ->selectRaw('waste_type')
            ->selectRaw('SUM(actual_weight) as total_weight')
            ->groupBy('date', 'waste_type')
            ->orderBy('date')
            ->get();
        
        // Initialize data arrays for each waste type
        $wasteTypes = ['general', 'recyclable', 'organic', 'hazardous', 'construction'];
        $dataByType = [];
        foreach ($wasteTypes as $type) {
            $dataByType[$type] = [];
        }
        
        // Prepare labels (dates)
        $labels = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('M j');
            
            // Initialize each type for this date
            foreach ($wasteTypes as $type) {
                $dataByType[$type][$dateKey] = 0;
            }
        }
        
        // Fill in actual data
        foreach ($collections as $collection) {
            if (isset($dataByType[$collection->waste_type])) {
                $dataByType[$collection->waste_type][$collection->date] = round($collection->total_weight, 1);
            }
        }
        
        // Convert to arrays for chart
        $datasets = [];
        
        if (array_sum($dataByType['general']) > 0) {
            $datasets[] = [
                'label' => 'General Waste',
                'data' => array_values($dataByType['general']),
                'backgroundColor' => 'rgba(107, 114, 128, 0.3)',
                'borderColor' => 'rgb(107, 114, 128)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.3,
            ];
        }
        
        if (array_sum($dataByType['recyclable']) > 0) {
            $datasets[] = [
                'label' => 'Recyclable',
                'data' => array_values($dataByType['recyclable']),
                'backgroundColor' => 'rgba(34, 197, 94, 0.3)',
                'borderColor' => 'rgb(34, 197, 94)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.3,
            ];
        }
        
        if (array_sum($dataByType['organic']) > 0) {
            $datasets[] = [
                'label' => 'Organic',
                'data' => array_values($dataByType['organic']),
                'backgroundColor' => 'rgba(251, 146, 60, 0.3)',
                'borderColor' => 'rgb(251, 146, 60)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.3,
            ];
        }
        
        if (array_sum($dataByType['hazardous']) > 0) {
            $datasets[] = [
                'label' => 'Hazardous',
                'data' => array_values($dataByType['hazardous']),
                'backgroundColor' => 'rgba(239, 68, 68, 0.3)',
                'borderColor' => 'rgb(239, 68, 68)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.3,
            ];
        }
        
        if (array_sum($dataByType['construction']) > 0) {
            $datasets[] = [
                'label' => 'Construction',
                'data' => array_values($dataByType['construction']),
                'backgroundColor' => 'rgba(59, 130, 246, 0.3)',
                'borderColor' => 'rgb(59, 130, 246)',
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.3,
            ];
        }
        
        return [
            'datasets' => $datasets,
            'labels' => $labels,
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
                                label += context.parsed.y.toFixed(1) + ' tons';
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
                    'ticks' => [
                        'maxRotation' => 45,
                        'minRotation' => 0,
                    ],
                ],
                'y' => [
                    'stacked' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Weight (tons)',
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}