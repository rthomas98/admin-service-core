<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class CustomerQuickActions extends Widget
{
    protected static ?int $sort = 2;

    protected string $view = 'filament.widgets.customer-quick-actions';

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    public function getActions(): array
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return [];
        }

        return [
            [
                'label' => 'New Customer',
                'icon' => 'heroicon-o-user-plus',
                'url' => '/admin/'.$tenant->id.'/customers/create',
                'color' => 'primary',
                'description' => 'Add a new customer to your database',
            ],
            [
                'label' => 'Create Invoice',
                'icon' => 'heroicon-o-document-plus',
                'url' => '/admin/'.$tenant->id.'/invoices/create',
                'color' => 'success',
                'description' => 'Generate a new invoice for billing',
            ],
            [
                'label' => 'New Quote',
                'icon' => 'heroicon-o-clipboard-document-list',
                'url' => '/admin/'.$tenant->id.'/quotes/create',
                'color' => 'info',
                'description' => 'Create a service quote',
            ],
            [
                'label' => 'Service Request',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'url' => '/admin/'.$tenant->id.'/service-requests/create',
                'color' => 'warning',
                'description' => 'Submit a new service request',
            ],
            [
                'label' => 'Invite User',
                'icon' => 'heroicon-o-envelope',
                'url' => '/admin/'.$tenant->id.'/company-user-invites/create',
                'color' => 'gray',
                'description' => 'Invite team members to the portal',
            ],
            [
                'label' => 'View Reports',
                'icon' => 'heroicon-o-chart-bar',
                'url' => '/admin/'.$tenant->id.'/reports',
                'color' => 'purple',
                'description' => 'Access business analytics',
            ],
        ];
    }
}
