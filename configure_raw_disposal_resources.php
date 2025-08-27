<?php

// Script to configure all RAW Disposal Filament resources
// This adds tenant scoping, proper navigation, and visibility controls

$resourceConfigs = [
    'Equipment' => [
        'icon' => 'Heroicon::Truck',
        'navigationSort' => 10,
        'navigationGroup' => 'Operations',
        'tenantScope' => true
    ],
    'ServiceOrder' => [
        'icon' => 'Heroicon::ClipboardDocumentList', 
        'navigationSort' => 20,
        'navigationGroup' => 'Orders',
        'navigationLabel' => 'Service Orders',
        'tenantScope' => true
    ],
    'Pricing' => [
        'icon' => 'Heroicon::CurrencyDollar',
        'navigationSort' => 30,
        'navigationGroup' => 'Configuration',
        'tenantScope' => true
    ],
    'DeliverySchedule' => [
        'icon' => 'Heroicon::CalendarDays',
        'navigationSort' => 40,
        'navigationGroup' => 'Scheduling',
        'navigationLabel' => 'Deliveries',
        'tenantScope' => true
    ],
    'ServiceSchedule' => [
        'icon' => 'Heroicon::Wrench',
        'navigationSort' => 50,
        'navigationGroup' => 'Scheduling',
        'navigationLabel' => 'Service',
        'tenantScope' => true
    ],
    'Driver' => [
        'icon' => 'Heroicon::Users',
        'navigationSort' => 60,
        'navigationGroup' => 'Team',
        'navigationLabel' => 'Drivers',
        'tenantScope' => true
    ],
    'Invoice' => [
        'icon' => 'Heroicon::DocumentText',
        'navigationSort' => 70,
        'navigationGroup' => 'Financial',
        'navigationLabel' => 'Invoices',
        'tenantScope' => true
    ],
    'Payment' => [
        'icon' => 'Heroicon::CreditCard',
        'navigationSort' => 80,
        'navigationGroup' => 'Financial',
        'navigationLabel' => 'Payments',
        'tenantScope' => true
    ],
    'Quote' => [
        'icon' => 'Heroicon::DocumentCheck',
        'navigationSort' => 90,
        'navigationGroup' => 'Sales',
        'navigationLabel' => 'Quotes',
        'tenantScope' => true
    ],
    'ServiceArea' => [
        'icon' => 'Heroicon::Map',
        'navigationSort' => 100,
        'navigationGroup' => 'Configuration',
        'navigationLabel' => 'Service Areas',
        'tenantScope' => true
    ],
    'MaintenanceLog' => [
        'icon' => 'Heroicon::WrenchScrewdriver',
        'navigationSort' => 110,
        'navigationGroup' => 'Operations',
        'navigationLabel' => 'Maintenance',
        'tenantScope' => true
    ],
    'EmergencyService' => [
        'icon' => 'Heroicon::ExclamationTriangle',
        'navigationSort' => 120,
        'navigationGroup' => 'Operations',
        'navigationLabel' => 'Emergency',
        'tenantScope' => true
    ]
];

echo "RAW Disposal Resource Configuration\n";
echo "====================================\n\n";

foreach ($resourceConfigs as $resource => $config) {
    echo "Configure {$resource}Resource:\n";
    echo "- Icon: {$config['icon']}\n";
    echo "- Navigation Group: " . ($config['navigationGroup'] ?? 'Default') . "\n";
    echo "- Navigation Label: " . ($config['navigationLabel'] ?? $resource . 's') . "\n";
    echo "- Tenant Scope: " . ($config['tenantScope'] ? 'Yes (RAW Disposal only)' : 'No') . "\n";
    echo "- Sort Order: {$config['navigationSort']}\n\n";
}

echo "All resources need:\n";
echo "1. Tenant scoping in getEloquentQuery()\n";
echo "2. canViewAny() to check for RAW Disposal tenant\n";
echo "3. mutateFormDataBeforeCreate() to set company_id\n";
echo "4. Proper form sections and table columns\n";
echo "5. Relationship managers where applicable\n";

?>