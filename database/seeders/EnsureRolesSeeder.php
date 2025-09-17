<?php

namespace Database\Seeders;

use App\Models\TeamInvite;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EnsureRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all roles defined in TeamInvite
        $availableRoles = TeamInvite::getAvailableRoles();

        foreach ($availableRoles as $roleName => $roleLabel) {
            // Create role if it doesn't exist
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['guard_name' => 'web']
            );

            $this->command->info("Ensured role exists: {$roleName} ({$roleLabel})");

            // Assign basic permissions based on role
            $this->assignRolePermissions($role, $roleName);
        }

        // Ensure customer role exists (not in TeamInvite but used in system)
        Role::firstOrCreate(
            ['name' => 'customer'],
            ['guard_name' => 'web']
        );
        $this->command->info('Ensured role exists: customer');

        // Clean up duplicate "Super Admin" role if it exists (should be super_admin)
        $duplicateRole = Role::where('name', 'Super Admin')->first();
        if ($duplicateRole) {
            // Transfer any users with "Super Admin" role to "super_admin"
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if ($superAdminRole) {
                foreach ($duplicateRole->users as $user) {
                    if (! $user->hasRole('super_admin')) {
                        $user->assignRole('super_admin');
                    }
                    $user->removeRole('Super Admin');
                }
            }
            $duplicateRole->delete();
            $this->command->info("Cleaned up duplicate 'Super Admin' role");
        }
    }

    /**
     * Assign appropriate permissions to each role.
     */
    protected function assignRolePermissions(Role $role, string $roleName): void
    {
        // Ensure basic permission exists
        $accessPanel = Permission::firstOrCreate(
            ['name' => 'access_admin_panel'],
            ['guard_name' => 'web']
        );

        // Assign permissions based on role
        switch ($roleName) {
            case 'super_admin':
                // Super admin gets all permissions
                $role->givePermissionTo(Permission::all());
                break;

            case 'admin':
                // Admin gets most permissions
                $role->givePermissionTo($accessPanel);
                // Add more admin-specific permissions as needed
                $this->createAndAssignPermission($role, 'manage_users');
                $this->createAndAssignPermission($role, 'manage_companies');
                $this->createAndAssignPermission($role, 'manage_vehicles');
                $this->createAndAssignPermission($role, 'manage_drivers');
                $this->createAndAssignPermission($role, 'manage_customers');
                $this->createAndAssignPermission($role, 'manage_invoices');
                break;

            case 'manager':
                // Manager gets operational permissions
                $role->givePermissionTo($accessPanel);
                $this->createAndAssignPermission($role, 'view_reports');
                $this->createAndAssignPermission($role, 'manage_schedules');
                $this->createAndAssignPermission($role, 'manage_service_orders');
                $this->createAndAssignPermission($role, 'view_invoices');
                break;

            case 'dispatcher':
                // Dispatcher manages daily operations
                $role->givePermissionTo($accessPanel);
                $this->createAndAssignPermission($role, 'manage_driver_assignments');
                $this->createAndAssignPermission($role, 'manage_schedules');
                $this->createAndAssignPermission($role, 'view_vehicles');
                $this->createAndAssignPermission($role, 'view_drivers');
                break;

            case 'driver':
                // Driver has limited access
                $role->givePermissionTo($accessPanel);
                $this->createAndAssignPermission($role, 'view_own_assignments');
                $this->createAndAssignPermission($role, 'update_delivery_status');
                $this->createAndAssignPermission($role, 'submit_vehicle_inspection');
                break;

            case 'accountant':
                // Accountant manages financial aspects
                $role->givePermissionTo($accessPanel);
                $this->createAndAssignPermission($role, 'manage_invoices');
                $this->createAndAssignPermission($role, 'manage_payments');
                $this->createAndAssignPermission($role, 'view_financial_reports');
                $this->createAndAssignPermission($role, 'manage_quotes');
                break;

            case 'customer_service':
                // Customer service handles customer interactions
                $role->givePermissionTo($accessPanel);
                $this->createAndAssignPermission($role, 'manage_customers');
                $this->createAndAssignPermission($role, 'manage_service_orders');
                $this->createAndAssignPermission($role, 'view_invoices');
                $this->createAndAssignPermission($role, 'create_quotes');
                break;

            case 'viewer':
                // Viewer has read-only access
                $role->givePermissionTo($accessPanel);
                $this->createAndAssignPermission($role, 'view_dashboard');
                $this->createAndAssignPermission($role, 'view_reports');
                break;

            default:
                // Default: just panel access
                $role->givePermissionTo($accessPanel);
                break;
        }
    }

    /**
     * Create a permission if it doesn't exist and assign it to the role.
     */
    protected function createAndAssignPermission(Role $role, string $permissionName): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['guard_name' => 'web']
        );

        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
    }
}
