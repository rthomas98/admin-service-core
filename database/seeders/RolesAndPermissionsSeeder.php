<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for each resource
        $resources = [
            'users',
            'roles',
            'permissions',
            'companies',
            'customers',
            'drivers',
            'vehicles',
            'trailers',
            'equipment',
            'service_orders',
            'work_orders',
            'invoices',
            'payments',
            'quotes',
            'notifications',
            'maintenance_logs',
            'fuel_logs',
            'vehicle_inspections',
            'vehicle_maintenances',
            'waste_routes',
            'waste_collections',
            'disposal_sites',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::create(['name' => "{$action}_{$resource}"]);
            }
        }

        // Create additional specific permissions
        Permission::create(['name' => 'view_dashboard']);
        Permission::create(['name' => 'view_reports']);
        Permission::create(['name' => 'export_data']);
        Permission::create(['name' => 'import_data']);
        Permission::create(['name' => 'send_notifications']);
        Permission::create(['name' => 'manage_settings']);
        Permission::create(['name' => 'manage_company_users']);
        Permission::create(['name' => 'manage_customer_invites']);
        Permission::create(['name' => 'access_admin_panel']);
        Permission::create(['name' => 'access_customer_portal']);

        // Create roles and assign permissions

        // Super Admin - Full system access (RAW Disposal and LIV Transport owners)
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - Company-specific full access
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // Manager - Operational management access
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view_dashboard',
            'view_reports',
            'export_data',
            // Customers
            'view_customers',
            'create_customers',
            'update_customers',
            // Service Orders
            'view_service_orders',
            'create_service_orders',
            'update_service_orders',
            // Work Orders
            'view_work_orders',
            'create_work_orders',
            'update_work_orders',
            // Drivers
            'view_drivers',
            'update_drivers',
            // Vehicles
            'view_vehicles',
            'update_vehicles',
            // Equipment
            'view_equipment',
            'update_equipment',
            // Maintenance
            'view_maintenance_logs',
            'create_maintenance_logs',
            'update_maintenance_logs',
            'view_fuel_logs',
            'create_fuel_logs',
            'view_vehicle_inspections',
            'create_vehicle_inspections',
            'view_vehicle_maintenances',
            'create_vehicle_maintenances',
            // Invoices & Payments
            'view_invoices',
            'create_invoices',
            'update_invoices',
            'view_payments',
            'update_payments',
            // Quotes
            'view_quotes',
            'create_quotes',
            'update_quotes',
            // Notifications
            'send_notifications',
            'view_notifications',
            // Waste Management
            'view_waste_routes',
            'update_waste_routes',
            'view_waste_collections',
            'create_waste_collections',
            'update_waste_collections',
            'view_disposal_sites',
            'access_admin_panel',
        ]);

        // Dispatcher - Schedule and route management
        $dispatcher = Role::create(['name' => 'dispatcher']);
        $dispatcher->givePermissionTo([
            'view_dashboard',
            'view_drivers',
            'update_drivers',
            'view_vehicles',
            'view_service_orders',
            'create_service_orders',
            'update_service_orders',
            'view_work_orders',
            'create_work_orders',
            'update_work_orders',
            'view_waste_routes',
            'create_waste_routes',
            'update_waste_routes',
            'view_waste_collections',
            'create_waste_collections',
            'send_notifications',
            'access_admin_panel',
        ]);

        // Accountant - Financial management
        $accountant = Role::create(['name' => 'accountant']);
        $accountant->givePermissionTo([
            'view_dashboard',
            'view_reports',
            'export_data',
            'view_customers',
            'view_invoices',
            'create_invoices',
            'update_invoices',
            'view_payments',
            'create_payments',
            'update_payments',
            'view_quotes',
            'update_quotes',
            'access_admin_panel',
        ]);

        // Driver - Limited access for field workers
        $driver = Role::create(['name' => 'driver']);
        $driver->givePermissionTo([
            'view_dashboard',
            'view_service_orders',
            'update_service_orders',
            'view_work_orders',
            'update_work_orders',
            'view_waste_routes',
            'view_waste_collections',
            'update_waste_collections',
            'create_fuel_logs',
            'create_vehicle_inspections',
            'access_admin_panel',
        ]);

        // Customer Service - Customer interaction management
        $customerService = Role::create(['name' => 'customer_service']);
        $customerService->givePermissionTo([
            'view_dashboard',
            'view_customers',
            'create_customers',
            'update_customers',
            'view_service_orders',
            'create_service_orders',
            'update_service_orders',
            'view_quotes',
            'create_quotes',
            'update_quotes',
            'view_invoices',
            'view_payments',
            'send_notifications',
            'manage_customer_invites',
            'access_admin_panel',
        ]);

        // Viewer - Read-only access
        $viewer = Role::create(['name' => 'viewer']);
        $viewer->givePermissionTo([
            'view_dashboard',
            'view_reports',
            'view_customers',
            'view_drivers',
            'view_vehicles',
            'view_service_orders',
            'view_work_orders',
            'view_invoices',
            'view_payments',
            'view_quotes',
            'access_admin_panel',
        ]);

        // Customer - Portal access only (for customers)
        $customer = Role::create(['name' => 'customer']);
        $customer->givePermissionTo([
            'access_customer_portal',
        ]);
    }
}
