<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class InvoicePaymentStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant) {
            return [];
        }
        
        // Paid invoices
        $paidInvoices = Invoice::where('company_id', $tenant->id)
            ->where('status', 'paid')
            ->whereMonth('paid_date', Carbon::now()->month)
            ->whereYear('paid_date', Carbon::now()->year);
            
        $paidCount = $paidInvoices->count();
        $paidAmount = $paidInvoices->sum('total_amount');
        
        // Pending invoices (sent but not paid)
        $pendingInvoices = Invoice::where('company_id', $tenant->id)
            ->whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '>=', Carbon::today());
            
        $pendingCount = $pendingInvoices->count();
        $pendingAmount = $pendingInvoices->sum('balance_due');
        
        // Overdue invoices
        $overdueInvoices = Invoice::where('company_id', $tenant->id)
            ->whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '<', Carbon::today());
            
        $overdueCount = $overdueInvoices->count();
        $overdueAmount = $overdueInvoices->sum('balance_due');
        
        // Draft invoices (not sent)
        $draftInvoices = Invoice::where('company_id', $tenant->id)
            ->where('status', 'draft');
            
        $draftCount = $draftInvoices->count();
        $draftAmount = $draftInvoices->sum('total_amount');
        
        // Collection rate this month
        $totalBilledThisMonth = Invoice::where('company_id', $tenant->id)
            ->whereMonth('invoice_date', Carbon::now()->month)
            ->whereYear('invoice_date', Carbon::now()->year)
            ->sum('total_amount');
            
        $collectionRate = $totalBilledThisMonth > 0 
            ? round(($paidAmount / $totalBilledThisMonth) * 100, 1)
            : 0;
        
        return [
            Stat::make('Paid This Month', '$' . Number::format($paidAmount, 2))
                ->description($paidCount . ' invoices paid')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([
                    $paidAmount * 0.7,
                    $paidAmount * 0.8,
                    $paidAmount * 0.9,
                    $paidAmount,
                ])
                ->extraAttributes([
                    'class' => 'ring-2 ring-green-500/20',
                ]),
                
            Stat::make('Pending Payment', '$' . Number::format($pendingAmount, 2))
                ->description($pendingCount . ' invoices pending')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->url('/admin/' . $tenant->id . '/invoices?tableFilters[status][value]=sent'),
                
            Stat::make('Overdue', '$' . Number::format($overdueAmount, 2))
                ->description($overdueCount . ' invoices overdue')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->extraAttributes([
                    'class' => $overdueCount > 0 ? 'ring-2 ring-red-500/20 animate-pulse' : '',
                ])
                ->url('/admin/' . $tenant->id . '/invoices?tableFilters[overdue][value]=true'),
                
            Stat::make('Collection Rate', $collectionRate . '%')
                ->description('This month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($collectionRate >= 80 ? 'success' : ($collectionRate >= 60 ? 'warning' : 'danger'))
                ->chart([
                    $collectionRate * 0.8,
                    $collectionRate * 0.9,
                    $collectionRate,
                ]),
        ];
    }
}