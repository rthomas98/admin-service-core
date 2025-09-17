<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MakeFilamentAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:make-filament-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make rob.thomas@empuls3.com a Filament admin with all necessary permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==== Making User a Filament Admin ====');

        $email = 'rob.thomas@empuls3.com';

        // Step 1: Find the user
        $this->info("\n1. Finding user...");
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found!");
            return 1;
        }

        $this->info("✅ User found: ID {$user->id}");

        // Step 2: Create 'Super Admin' role if it doesn't exist
        $this->info("\n2. Creating/finding Super Admin role...");
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['guard_name' => 'web']
        );
        $this->info("✅ Super Admin role ready: ID {$superAdminRole->id}");

        // Step 3: Create 'access_admin_panel' permission if it doesn't exist
        $this->info("\n3. Creating/finding access_admin_panel permission...");
        $accessPermission = Permission::firstOrCreate(
            ['name' => 'access_admin_panel'],
            ['guard_name' => 'web']
        );
        $this->info("✅ Permission ready: ID {$accessPermission->id}");

        // Step 4: Assign permission to role
        $this->info("\n4. Assigning permission to Super Admin role...");
        if (!$superAdminRole->hasPermissionTo('access_admin_panel')) {
            $superAdminRole->givePermissionTo('access_admin_panel');
            $this->info("✅ Permission assigned to role");
        } else {
            $this->info("✅ Role already has permission");
        }

        // Step 5: Assign role to user
        $this->info("\n5. Assigning Super Admin role to user...");
        if (!$user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
            $this->info("✅ Role assigned to user");
        } else {
            $this->info("✅ User already has role");
        }

        // Step 6: Also give direct permission as backup
        $this->info("\n6. Giving direct permission to user...");
        if (!$user->hasPermissionTo('access_admin_panel')) {
            $user->givePermissionTo('access_admin_panel');
            $this->info("✅ Direct permission given");
        } else {
            $this->info("✅ User already has permission");
        }

        // Step 7: Verify companies are set as service_provider type
        $this->info("\n7. Checking company types...");
        $companies = DB::table('companies')
            ->whereIn('id', [1, 2])
            ->get(['id', 'name', 'company_type']);

        foreach ($companies as $company) {
            $this->info("Company {$company->id} ({$company->name}): type = " . ($company->company_type ?? 'NULL'));

            if ($company->company_type !== 'service_provider') {
                DB::table('companies')
                    ->where('id', $company->id)
                    ->update(['company_type' => 'service_provider']);
                $this->info("  ✅ Updated to service_provider");
            }
        }

        // Step 8: Test canAccessPanel
        $this->info("\n8. Testing canAccessPanel method...");
        try {
            $panel = \Filament\Facades\Filament::getPanel('admin');
            $canAccess = $user->canAccessPanel($panel);
            $this->info("canAccessPanel result: " . ($canAccess ? '✅ YES' : '❌ NO'));

            if (!$canAccess) {
                $this->info("\nDebugging why access is denied:");
                $this->info("- Has 'Super Admin' role: " . ($user->hasRole('Super Admin') ? 'YES' : 'NO'));
                $this->info("- Has 'access_admin_panel' permission: " . ($user->hasPermissionTo('access_admin_panel') ? 'YES' : 'NO'));
                $this->info("- Can 'access_admin_panel': " . ($user->can('access_admin_panel') ? 'YES' : 'NO'));
            }
        } catch (\Exception $e) {
            $this->warn("Could not test canAccessPanel: " . $e->getMessage());
        }

        // Step 9: Clear permission cache
        $this->info("\n9. Clearing permission cache...");
        $this->call('permission:cache-reset');
        $this->info("✅ Permission cache cleared");

        // Step 10: Clear all caches
        $this->info("\n10. Clearing all caches...");
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');
        $this->call('route:clear');
        $this->info("✅ All caches cleared");

        // Final summary
        $this->info("\n==== Summary ====");
        $this->info("User: {$email}");
        $this->info("Roles: " . $user->getRoleNames()->implode(', '));
        $this->info("Direct Permissions: " . $user->getDirectPermissions()->pluck('name')->implode(', '));
        $this->info("All Permissions: " . $user->getAllPermissions()->pluck('name')->implode(', '));
        $this->info("Companies: " . $user->companies()->count());

        $this->info("\n✅ User should now be able to access Filament admin panel!");
        $this->info("\nLogin with:");
        $this->info("Email: {$email}");
        $this->info("Password: TestPassword123!");

        return 0;
    }
}