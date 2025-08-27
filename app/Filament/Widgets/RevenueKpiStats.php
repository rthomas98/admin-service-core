<?php

namespace App\Filament\Widgets;

use App\Models\DriverAssignment;
use App\Models\FuelLog;
use App\Models\VehicleFinance;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class RevenueKpiStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';
    
    protected ?string $pollingInterval = '30s';
    
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isLivTransport()) {
            return [];
        }
        
        // Current month calculations
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        
        // Revenue calculation (estimated based on cargo weight * rate)
        $currentMonthRevenue = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereMonth('end_date', $currentMonth->month)
            ->whereYear('end_date', $currentMonth->year)
            ->sum('cargo_weight') * 0.15; // $0.15 per kg
            
        $lastMonthRevenue = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereMonth('end_date', $lastMonth->month)
            ->whereYear('end_date', $lastMonth->year)
            ->sum('cargo_weight') * 0.15;
            
        $revenueChange = $lastMonthRevenue > 0 
            ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
            : 0;
            
        // Fuel costs
        $currentMonthFuelCost = FuelLog::where('company_id', $tenant->id)
            ->whereMonth('fuel_date', $currentMonth->month)
            ->whereYear('fuel_date', $currentMonth->year)
            ->sum('total_cost');
            
        $lastMonthFuelCost = FuelLog::where('company_id', $tenant->id)
            ->whereMonth('fuel_date', $lastMonth->month)
            ->whereYear('fuel_date', $lastMonth->year)
            ->sum('total_cost');
            
        // Finance payments
        $monthlyFinancePayments = VehicleFinance::where('company_id', $tenant->id)
            ->where('is_active', true)
            ->sum('monthly_payment');
            
        // Operating margin calculation
        $operatingCosts = $currentMonthFuelCost + $monthlyFinancePayments;
        $operatingMargin = $currentMonthRevenue > 0
            ? (($currentMonthRevenue - $operatingCosts) / $currentMonthRevenue) * 100
            : 0;
            
        // Average revenue per trip
        $completedTrips = DriverAssignment::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereMonth('end_date', $currentMonth->month)
            ->whereYear('end_date', $currentMonth->year)
            ->count();
            
        $avgRevenuePerTrip = $completedTrips > 0 
            ? $currentMonthRevenue / $completedTrips
            : 0;
        
        // Build trend data for charts
        $revenueTrend = collect(range(6, 0))->map(function ($monthsAgo) use ($tenant) {
            $date = Carbon::now()->subMonths($monthsAgo);
            return DriverAssignment::where('company_id', $tenant->id)
                ->where('status', 'completed')
                ->whereMonth('end_date', $date->month)
                ->whereYear('end_date', $date->year)
                ->count();
        })->toArray();
        
        return [
            Stat::make('Monthly Revenue', '$' . Number::abbreviate($currentMonthRevenue, precision: 2))
                ->description($revenueChange >= 0 
                    ? '↑ ' . Number::percentage($revenueChange, precision: 1) . ' from last month'
                    : '↓ ' . Number::percentage(abs($revenueChange), precision: 1) . ' from last month')
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($revenueTrend)
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:ring-2 hover:ring-primary-500 transition-all',
                    'wire:click' => '$dispatch("openUrl", {url: "/admin/3/driver-assignments"})',
                ]),
                
            Stat::make('Operating Margin', Number::percentage($operatingMargin, precision: 1))
                ->description('Revenue minus fuel & finance costs')
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($operatingMargin > 30 ? 'success' : ($operatingMargin > 15 ? 'warning' : 'danger'))
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:ring-2 hover:ring-primary-500 transition-all',
                ]),
                
            Stat::make('Fuel Costs', '$' . Number::abbreviate($currentMonthFuelCost, precision: 2))
                ->description($lastMonthFuelCost > 0
                    ? 'vs $' . Number::abbreviate($lastMonthFuelCost, precision: 2) . ' last month'
                    : 'This month\'s fuel expenses')
                ->descriptionIcon('heroicon-m-fire')
                ->chart(collect(range(6, 0))->map(function ($monthsAgo) use ($tenant) {
                    $date = Carbon::now()->subMonths($monthsAgo);
                    return (int) FuelLog::where('company_id', $tenant->id)
                        ->whereMonth('fuel_date', $date->month)
                        ->whereYear('fuel_date', $date->year)
                        ->sum('total_cost');
                })->toArray())
                ->color('warning')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:ring-2 hover:ring-primary-500 transition-all',
                    'wire:click' => '$dispatch("openUrl", {url: "/admin/3/fuel-logs"})',
                ]),
                
            Stat::make('Avg Revenue/Trip', '$' . Number::format($avgRevenuePerTrip, precision: 2))
                ->description($completedTrips . ' trips completed this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info')
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:ring-2 hover:ring-primary-500 transition-all',
                ]),
        ];
    }
}