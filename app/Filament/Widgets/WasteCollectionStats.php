<?php

namespace App\Filament\Widgets;

use App\Models\WasteCollection;
use App\Models\DisposalSite;
use App\Models\WasteRoute;
use App\Models\Customer;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class WasteCollectionStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant || !$tenant->isRawDisposal()) {
            return [];
        }
        
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        // Today's collections
        $todayCollections = WasteCollection::where('company_id', $tenant->id)
            ->whereDate('scheduled_date', $today)
            ->count();
            
        $yesterdayCollections = WasteCollection::where('company_id', $tenant->id)
            ->whereDate('scheduled_date', $yesterday)
            ->count();
            
        $collectionChange = $yesterdayCollections > 0 
            ? round((($todayCollections - $yesterdayCollections) / $yesterdayCollections) * 100, 1) 
            : 0;
            
        // Total waste collected this month (in tons)
        $monthStart = Carbon::now()->startOfMonth();
        $totalWasteThisMonth = WasteCollection::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$monthStart, Carbon::now()])
            ->sum('actual_weight');
            
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $totalWasteLastMonth = WasteCollection::where('company_id', $tenant->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('actual_weight');
            
        $wasteChange = $totalWasteLastMonth > 0
            ? round((($totalWasteThisMonth - $totalWasteLastMonth) / $totalWasteLastMonth) * 100, 1)
            : 0;
            
        // Active disposal sites
        $activeSites = DisposalSite::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->count();
            
        $sitesNearCapacity = DisposalSite::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->whereRaw('current_capacity >= total_capacity * 0.85')
            ->count();
            
        // Active customers (all customers for now, no status field)
        $activeCustomers = Customer::where('company_id', $tenant->id)
            ->count();
            
        $newCustomersThisMonth = Customer::where('company_id', $tenant->id)
            ->where('created_at', '>=', $monthStart)
            ->count();
        
        return [
            Stat::make("Today's Collections", $todayCollections)
                ->description($collectionChange >= 0 ? "↑ {$collectionChange}% from yesterday" : "↓ " . abs($collectionChange) . "% from yesterday")
                ->descriptionIcon($collectionChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($collectionChange >= 0 ? 'success' : 'warning')
                ->chart([
                    $yesterdayCollections,
                    $todayCollections,
                ])
                ->url('/admin/1/waste-collections'),
                
            Stat::make('Waste Collected', number_format($totalWasteThisMonth, 1) . ' tons')
                ->description($wasteChange >= 0 ? "↑ {$wasteChange}% from last month" : "↓ " . abs($wasteChange) . "% from last month")
                ->descriptionIcon($wasteChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($wasteChange >= 0 ? 'success' : 'danger')
                ->extraAttributes([
                    'class' => 'ring-2 ring-green-500/20',
                ]),
                
            Stat::make('Active Sites', $activeSites . ' operational')
                ->description($sitesNearCapacity > 0 ? "{$sitesNearCapacity} near capacity" : "All sites operational")
                ->descriptionIcon($sitesNearCapacity > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($sitesNearCapacity > 0 ? 'warning' : 'success')
                ->url('/admin/1/disposal-sites'),
                
            Stat::make('Active Customers', $activeCustomers)
                ->description("+{$newCustomersThisMonth} new this month")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary')
                ->chart([
                    $activeCustomers - $newCustomersThisMonth,
                    $activeCustomers,
                ])
                ->url('/admin/1/customers'),
        ];
    }
}