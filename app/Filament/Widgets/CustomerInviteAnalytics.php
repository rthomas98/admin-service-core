<?php

namespace App\Filament\Widgets;

use App\Models\CustomerInvite;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CustomerInviteAnalytics extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Get statistics based on current company context
        $companyId = Auth::user()?->current_company_id;
        $stats = $this->getInviteStatistics($companyId);

        // Get time-based analytics
        $today = $this->getTodayStatistics($companyId);
        $thisWeek = $this->getWeekStatistics($companyId);
        $thisMonth = $this->getMonthStatistics($companyId);

        return [
            Stat::make('Total Invitations', $stats['total'])
                ->description($this->formatChange($thisMonth['total_change']).' this month')
                ->descriptionIcon($thisMonth['total_change'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($thisMonth['total_change'] >= 0 ? 'success' : 'warning')
                ->chart($this->getMonthlyChart('total')),

            Stat::make('Acceptance Rate', $stats['acceptance_rate'].'%')
                ->description($this->formatPercentageChange($thisMonth['acceptance_rate_change']).' this month')
                ->descriptionIcon($thisMonth['acceptance_rate_change'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($this->getAcceptanceRateColor($stats['acceptance_rate']))
                ->chart($this->getMonthlyChart('acceptance_rate')),

            Stat::make('Pending Invitations', $stats['pending'])
                ->description($today['pending_new'].' sent today')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart($this->getWeeklyChart('pending')),

            Stat::make('Expired Invitations', $stats['expired'])
                ->description($thisWeek['expired_new'].' expired this week')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->extraAttributes([
                    'class' => $stats['expired'] > 10 ? 'ring-2 ring-danger-500' : '',
                ]),
        ];
    }

    protected function getInviteStatistics(?int $companyId): array
    {
        $query = CustomerInvite::query();

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $total = $query->count();
        $accepted = $query->clone()->accepted()->count();
        $pending = $query->clone()->pending()->count();
        $expired = $query->clone()->expired()->count();

        return [
            'total' => $total,
            'accepted' => $accepted,
            'pending' => $pending,
            'expired' => $expired,
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 2) : 0,
        ];
    }

    protected function getTodayStatistics(?int $companyId): array
    {
        $query = CustomerInvite::query()
            ->whereDate('created_at', today());

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return [
            'total_new' => $query->count(),
            'pending_new' => $query->clone()->pending()->count(),
            'accepted_new' => $query->clone()->accepted()->count(),
        ];
    }

    protected function getWeekStatistics(?int $companyId): array
    {
        $query = CustomerInvite::query()
            ->where('created_at', '>=', now()->startOfWeek());

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $weekExpired = CustomerInvite::query()
            ->where('expires_at', '>=', now()->startOfWeek())
            ->where('expires_at', '<=', now())
            ->whereNull('accepted_at');

        if ($companyId) {
            $weekExpired->where('company_id', $companyId);
        }

        return [
            'total_new' => $query->count(),
            'accepted_new' => $query->clone()->accepted()->count(),
            'expired_new' => $weekExpired->count(),
        ];
    }

    protected function getMonthStatistics(?int $companyId): array
    {
        $currentMonth = CustomerInvite::query()
            ->where('created_at', '>=', now()->startOfMonth());

        $lastMonth = CustomerInvite::query()
            ->whereBetween('created_at', [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ]);

        if ($companyId) {
            $currentMonth->where('company_id', $companyId);
            $lastMonth->where('company_id', $companyId);
        }

        $currentTotal = $currentMonth->count();
        $lastTotal = $lastMonth->count();

        $currentAccepted = $currentMonth->clone()->accepted()->count();
        $lastAccepted = $lastMonth->clone()->accepted()->count();

        $currentRate = $currentTotal > 0 ? round(($currentAccepted / $currentTotal) * 100, 2) : 0;
        $lastRate = $lastTotal > 0 ? round(($lastAccepted / $lastTotal) * 100, 2) : 0;

        return [
            'total_change' => $currentTotal - $lastTotal,
            'acceptance_rate_change' => $currentRate - $lastRate,
        ];
    }

    protected function getMonthlyChart(string $metric): array
    {
        // Generate sample chart data for the last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $query = CustomerInvite::whereDate('created_at', $date);

            if ($companyId = Auth::user()?->current_company_id) {
                $query->where('company_id', $companyId);
            }

            if ($metric === 'total') {
                $data[] = $query->count();
            } elseif ($metric === 'acceptance_rate') {
                $total = $query->count();
                $accepted = $query->clone()->accepted()->count();
                $data[] = $total > 0 ? round(($accepted / $total) * 100, 0) : 0;
            }
        }

        return $data;
    }

    protected function getWeeklyChart(string $metric): array
    {
        // Generate sample chart data for the last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $query = CustomerInvite::whereDate('created_at', $date);

            if ($companyId = Auth::user()?->current_company_id) {
                $query->where('company_id', $companyId);
            }

            if ($metric === 'pending') {
                $data[] = $query->pending()->count();
            }
        }

        return $data;
    }

    protected function formatChange(int $change): string
    {
        if ($change > 0) {
            return '+'.$change;
        }

        return (string) $change;
    }

    protected function formatPercentageChange(float $change): string
    {
        $formatted = number_format(abs($change), 1);

        if ($change > 0) {
            return '+'.$formatted.'%';
        } elseif ($change < 0) {
            return '-'.$formatted.'%';
        }

        return '0%';
    }

    protected function getAcceptanceRateColor(float $rate): string
    {
        if ($rate >= 70) {
            return 'success';
        } elseif ($rate >= 40) {
            return 'warning';
        }

        return 'danger';
    }

    public static function canView(): bool
    {
        $tenant = \Filament\Facades\Filament::getTenant();

        // Only show for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
}
