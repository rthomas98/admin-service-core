<?php

namespace App\Filament\Widgets;

use App\Models\CompanyUser;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\ServiceRequest;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CustomerOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return [];
        }

        // Total customers
        $totalCustomers = Customer::where('company_id', $tenant->id)->count();
        // Count customers with recent activity (had service orders in last 3 months)
        $activeCustomers = Customer::where('company_id', $tenant->id)
            ->whereHas('serviceOrders', function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subMonths(3));
            })
            ->count();

        // Total invoices
        $totalInvoices = Invoice::where('company_id', $tenant->id)->count();
        $unpaidInvoices = Invoice::where('company_id', $tenant->id)
            ->whereIn('status', ['sent', 'partially_paid'])
            ->count();

        // Total quotes
        $totalQuotes = Quote::where('company_id', $tenant->id)->count();
        $pendingQuotes = Quote::where('company_id', $tenant->id)
            ->where('status', 'pending')
            ->count();

        // Service requests (if model exists)
        $serviceRequests = 0;
        $openRequests = 0;
        if (class_exists('\App\Models\ServiceRequest')) {
            $serviceRequests = ServiceRequest::where('company_id', $tenant->id)->count();
            $openRequests = ServiceRequest::where('company_id', $tenant->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
        }

        // Company users
        $totalUsers = CompanyUser::where('company_id', $tenant->id)
            ->where('is_active', true)
            ->count();

        // Calculate month-over-month growth for customers
        $lastMonthCustomers = Customer::where('company_id', $tenant->id)
            ->where('created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
            ->where('created_at', '<', Carbon::now()->startOfMonth())
            ->count();

        $thisMonthCustomers = Customer::where('company_id', $tenant->id)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        $customerGrowth = $lastMonthCustomers > 0
            ? round((($thisMonthCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 1)
            : 0;

        return [
            Stat::make('Total Customers', $totalCustomers)
                ->description($activeCustomers.' active')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary')
                ->chart([
                    max(0, $totalCustomers - 3),
                    max(0, $totalCustomers - 2),
                    max(0, $totalCustomers - 1),
                    $totalCustomers,
                ])
                ->url('/admin/'.$tenant->id.'/customers'),

            Stat::make('Open Invoices', $unpaidInvoices)
                ->description($totalInvoices.' total invoices')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($unpaidInvoices > 0 ? 'warning' : 'success')
                ->url('/admin/'.$tenant->id.'/invoices'),

            Stat::make('Pending Quotes', $pendingQuotes)
                ->description($totalQuotes.' total quotes')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->url('/admin/'.$tenant->id.'/quotes'),

            Stat::make('Service Requests', $openRequests)
                ->description($serviceRequests.' total requests')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color($openRequests > 0 ? 'warning' : 'success')
                ->url('/admin/'.$tenant->id.'/service-requests'),

            Stat::make('Team Members', $totalUsers)
                ->description('Active users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->url('/admin/'.$tenant->id.'/company-users'),

            Stat::make('Customer Growth', ($customerGrowth >= 0 ? '+' : '').$customerGrowth.'%')
                ->description('Month over month')
                ->descriptionIcon($customerGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($customerGrowth >= 0 ? 'success' : 'danger')
                ->chart([
                    $lastMonthCustomers,
                    $thisMonthCustomers,
                ]),
        ];
    }
}
