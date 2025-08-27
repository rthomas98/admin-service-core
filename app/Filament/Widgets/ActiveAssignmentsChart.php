<?php

namespace App\Filament\Widgets;

use App\Models\DriverAssignment;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ActiveAssignmentsChart extends ChartWidget
{
    protected ?string $heading = 'Assignment Activity - Last 7 Days';
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'full';
    
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
        
        $days = collect(range(6, 0))->map(function ($daysAgo) use ($tenant) {
            $date = Carbon::now()->subDays($daysAgo);
            
            return [
                'date' => $date,
                'active' => DriverAssignment::where('company_id', $tenant->id)
                    ->where('status', 'active')
                    ->whereDate('start_date', '<=', $date)
                    ->where(function ($query) use ($date) {
                        $query->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', $date);
                    })
                    ->count(),
                'completed' => DriverAssignment::where('company_id', $tenant->id)
                    ->where('status', 'completed')
                    ->whereDate('end_date', $date)
                    ->count(),
                'scheduled' => DriverAssignment::where('company_id', $tenant->id)
                    ->where('status', 'scheduled')
                    ->whereDate('start_date', $date)
                    ->count(),
            ];
        });
        
        return [
            'datasets' => [
                [
                    'label' => 'Active Assignments',
                    'data' => $days->pluck('active')->toArray(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Completed',
                    'data' => $days->pluck('completed')->toArray(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'tension' => 0.3,
                ],
                [
                    'label' => 'Scheduled',
                    'data' => $days->pluck('scheduled')->toArray(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(251, 146, 60, 0.1)',
                    'borderColor' => 'rgb(251, 146, 60)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $days->map(fn ($day) => $day['date']->format('M j'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 10,
                    ],
                ],
            ],
        ];
    }
}