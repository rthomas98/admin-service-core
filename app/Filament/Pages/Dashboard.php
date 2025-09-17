<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return [];
        }

        // Raw Disposal widgets
        if ($tenant->isRawDisposal()) {
            return [
                \App\Filament\Widgets\WasteCollectionStats::class,
                \App\Filament\Widgets\CustomerInviteAnalytics::class,
                \App\Filament\Widgets\DisposalSitesOverview::class,
                \App\Filament\Widgets\WasteVolumeChart::class,
                \App\Filament\Widgets\CollectionScheduleWidget::class,
            ];
        }

        // LIV Transport widgets
        if ($tenant->isLivTransport()) {
            return [
                \App\Filament\Widgets\FleetOverviewStats::class,
                \App\Filament\Widgets\RevenueKpiStats::class,
                \App\Filament\Widgets\EmergencyServicesAlert::class,
                \App\Filament\Widgets\DriverPerformanceChart::class,
                \App\Filament\Widgets\FuelConsumptionChart::class,
                \App\Filament\Widgets\FleetUtilizationChart::class,
                \App\Filament\Widgets\RecentDriverAssignments::class,
            ];
        }

        // Customer company widgets
        return [
            \App\Filament\Widgets\CustomerWelcomeWidget::class,
            \App\Filament\Widgets\CustomerOverviewStats::class,
            \App\Filament\Widgets\CustomerQuickActions::class,
            \App\Filament\Widgets\InvoicePaymentStats::class,
            \App\Filament\Widgets\RecentCustomersTable::class,
            \App\Filament\Widgets\PendingInvoicesTable::class,
        ];
    }

    public function getTitle(): string
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            return $tenant->name.' Dashboard';
        }

        return 'Dashboard';
    }

    public function getHeading(): string
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            if ($tenant->isRawDisposal()) {
                return '♻️ '.$tenant->name.' - Waste Management Dashboard';
            }

            if ($tenant->isLivTransport()) {
                return '🚛 '.$tenant->name.' - Transport Operations Dashboard';
            }

            return '📊 '.$tenant->name.' - Customer Portal';
        }

        return 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            $now = now()->format('l, F j, Y');

            if ($tenant->isRawDisposal()) {
                return "Waste collection and disposal operations • {$now}";
            }

            if ($tenant->isLivTransport()) {
                return "Fleet management and logistics • {$now}";
            }

            return "Customer services and billing • {$now}";
        }

        return null;
    }
}
