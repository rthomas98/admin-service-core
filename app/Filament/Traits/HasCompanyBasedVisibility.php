<?php

namespace App\Filament\Traits;

use Filament\Facades\Filament;

trait HasCompanyBasedVisibility
{
    /**
     * Determine if the resource should be shown in navigation based on company type
     */
    public static function shouldRegisterNavigation(): bool
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return false;
        }

        // Define which company types can see each resource group
        $resourceVisibility = [
            // Service provider companies (main admin) - can see everything
            'service_provider' => 'all',

            // Customer companies can see these resources
            'customer' => [
                'Customer',  // Changed from Customers to Customer
                'CompanyUsers',
                'Invoice',  // Changed from Invoices to Invoice to match resource name
                'Quote',
                'ServiceRequest',
                'Payment',
            ],
            // Transport companies (LIV Transport)
            'transport' => [
                'Vehicle',
                'Driver',
                'DriverAssignment',
                'VehicleInspection',
                'VehicleMaintenance',
                'VehicleRegistration',
                'FuelLog',
                'MaintenanceLog',
                'Customer',  // Changed from Customers to Customer
                'Invoice',  // Changed from Invoices to Invoice
                'Quote',
                'ServiceOrder',
                'DeliverySchedule',
                'EmergencyService',
            ],
            // Disposal companies (RAW Disposal)
            'disposal' => [
                'Customer',  // Changed from Customers to Customer
                'ServiceSchedule',
                'WasteRoute',
                'WasteCollection',
                'DisposalSite',
                'Equipment',
                'Invoice',  // Changed from Invoices to Invoice
                'Quote',
                'WorkOrder',  // Added WorkOrder for disposal companies
            ],
            // Companies that do both
            'both' => 'all', // Can see everything
        ];

        $companyType = $tenant->type ?? 'customer';
        $resourceName = class_basename(static::class);
        $resourceName = str_replace('Resource', '', $resourceName);

        // If company type is 'both', show all resources
        if ($companyType === 'both' || ($resourceVisibility[$companyType] ?? null) === 'all') {
            return true;
        }

        // Check if this resource is in the allowed list for this company type
        $allowedResources = $resourceVisibility[$companyType] ?? [];

        return in_array($resourceName, $allowedResources);
    }
}
