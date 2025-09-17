<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DebugAdminLogin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:debug-admin-login';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug and fix admin login issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==== Admin Login Debug ====');

        $email = 'rob.thomas@empuls3.com';
        $password = 'G00dBoySpot!!1013';

        // Step 1: Check if user exists
        $this->info("\n1. Checking user existence...");
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found!");
            $this->info("Creating user...");
            $user = User::create([
                'name' => 'Rob Thomas',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->info("✅ User created with ID: {$user->id}");
        } else {
            $this->info("✅ User found with ID: {$user->id}");
        }

        // Step 2: Check password
        $this->info("\n2. Checking password...");
        if (!Hash::check($password, $user->password)) {
            $this->error("❌ Password incorrect!");
            $this->info("Updating password...");
            $user->update(['password' => Hash::make($password)]);
            $this->info("✅ Password updated");
        } else {
            $this->info("✅ Password is correct");
        }

        // Step 3: Check company associations
        $this->info("\n3. Checking company associations...");
        $companies = $user->companies()->get();
        $this->info("Current companies: " . $companies->count());

        foreach ($companies as $company) {
            $this->info("  - {$company->name} (ID: {$company->id}, Role: {$company->pivot->role})");
        }

        // Step 4: Ensure companies exist
        $this->info("\n4. Ensuring companies exist...");

        // Check/Create LIV Transport
        $livTransport = DB::table('companies')
            ->where('slug', 'liv-transport')
            ->first();

        if (!$livTransport) {
            $this->info("Creating LIV Transport LLC...");
            $livTransportId = DB::table('companies')->insertGetId([
                'name' => 'LIV Transport LLC',
                'slug' => 'liv-transport',
                'type' => 'service',
                'email' => 'info@livtransport.com',
                'phone' => '555-0200',
                'address' => '456 Transport Way',
                'city' => 'Houston',
                'state' => 'TX',
                'zip' => '77002',
                'country' => 'USA',
                'primary_color' => '#2C3E50',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("✅ Created LIV Transport with ID: {$livTransportId}");
        } else {
            $livTransportId = $livTransport->id;
            $this->info("✅ LIV Transport exists with ID: {$livTransportId}");
        }

        // Check/Create RAW Disposal
        $rawDisposal = DB::table('companies')
            ->where('slug', 'raw-disposal')
            ->first();

        if (!$rawDisposal) {
            $this->info("Creating RAW Disposal LLC...");
            $rawDisposalId = DB::table('companies')->insertGetId([
                'name' => 'RAW Disposal LLC',
                'slug' => 'raw-disposal',
                'type' => 'service',
                'email' => 'info@rawdisposal.com',
                'phone' => '555-0100',
                'address' => '123 Main St',
                'city' => 'Houston',
                'state' => 'TX',
                'zip' => '77001',
                'country' => 'USA',
                'primary_color' => '#5C2C86',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("✅ Created RAW Disposal with ID: {$rawDisposalId}");
        } else {
            $rawDisposalId = $rawDisposal->id;
            $this->info("✅ RAW Disposal exists with ID: {$rawDisposalId}");
        }

        // Step 5: Attach user to companies
        $this->info("\n5. Attaching user to companies...");

        // Attach to LIV Transport
        $hasLivTransport = DB::table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $livTransportId)
            ->exists();

        if (!$hasLivTransport) {
            DB::table('company_user')->insert([
                'user_id' => $user->id,
                'company_id' => $livTransportId,
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("✅ Attached to LIV Transport");
        } else {
            $this->info("✅ Already attached to LIV Transport");
        }

        // Attach to RAW Disposal
        $hasRawDisposal = DB::table('company_user')
            ->where('user_id', $user->id)
            ->where('company_id', $rawDisposalId)
            ->exists();

        if (!$hasRawDisposal) {
            DB::table('company_user')->insert([
                'user_id' => $user->id,
                'company_id' => $rawDisposalId,
                'role' => 'super_admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("✅ Attached to RAW Disposal");
        } else {
            $this->info("✅ Already attached to RAW Disposal");
        }

        // Step 6: Test authentication
        $this->info("\n6. Testing authentication...");
        $attempt = Auth::attempt(['email' => $email, 'password' => $password]);

        if ($attempt) {
            $this->info("✅ Authentication successful!");
            $authUser = Auth::user();
            $this->info("   User ID: {$authUser->id}");
            $this->info("   Companies: " . $authUser->companies()->count());

            // Test getTenants method
            try {
                $panel = \Filament\Facades\Filament::getPanel('admin');
                if ($panel) {
                    $tenants = $authUser->getTenants($panel);
                    $this->info("   Tenants: " . $tenants->count());
                }
            } catch (\Exception $e) {
                $this->warn("   Could not test getTenants: " . $e->getMessage());
            }

            Auth::logout();
        } else {
            $this->error("❌ Authentication failed!");
        }

        // Step 7: Clear caches
        $this->info("\n7. Clearing caches...");
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        $this->info("\n==== Debug Complete ====");
        $this->info("\nLogin credentials:");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");

        return 0;
    }
}