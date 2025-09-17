<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DirectAuthTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:direct-auth-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Direct test of Laravel authentication bypassing Filament';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==== Direct Authentication Test ====');

        $email = 'rob.thomas@empuls3.com';
        $password = 'TestPassword123!';

        // Test 1: Direct Eloquent Model Test
        $this->info("\n1. Testing via Eloquent Model:");
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User not found via Eloquent!");

            // Try to create one
            $this->info("Creating user via Eloquent...");
            $user = User::create([
                'name' => 'Rob Thomas',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->info("✅ User created with ID: {$user->id}");
        } else {
            $this->info("✅ User found with ID: {$user->id}");

            // Update password to be sure
            $user->password = Hash::make($password);
            $user->save();
            $this->info("✅ Password updated via Eloquent");
        }

        // Test 2: Verify password hash
        $this->info("\n2. Password hash verification:");
        $isValid = Hash::check($password, $user->password);
        $this->info("Password '{$password}' validates: " . ($isValid ? '✅ YES' : '❌ NO'));

        // Test 3: Test Auth::attempt
        $this->info("\n3. Testing Auth::attempt():");
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        $result = Auth::attempt($credentials);
        $this->info("Auth::attempt() result: " . ($result ? '✅ SUCCESS' : '❌ FAILED'));

        if ($result) {
            $this->info("Logged in as: " . Auth::user()->email);
            Auth::logout();
            $this->info("Logged out successfully");
        }

        // Test 4: Test with guard explicitly
        $this->info("\n4. Testing with 'web' guard explicitly:");
        $result = Auth::guard('web')->attempt($credentials);
        $this->info("Auth::guard('web')->attempt() result: " . ($result ? '✅ SUCCESS' : '❌ FAILED'));

        if ($result) {
            Auth::guard('web')->logout();
        }

        // Test 5: Manual authentication
        $this->info("\n5. Manual authentication test:");
        $user = User::where('email', $email)->first();
        if ($user && Hash::check($password, $user->password)) {
            $this->info("✅ Manual check passed");

            // Try to login manually
            Auth::login($user);
            if (Auth::check()) {
                $this->info("✅ Manual login successful");
                $this->info("Authenticated user: " . Auth::user()->email);
                Auth::logout();
            } else {
                $this->error("❌ Manual login failed");
            }
        } else {
            $this->error("❌ Manual check failed");
        }

        // Test 6: Check if there's a guard issue
        $this->info("\n6. Guard configuration check:");
        $defaultGuard = config('auth.defaults.guard');
        $this->info("Default guard: {$defaultGuard}");

        $guardConfig = config("auth.guards.{$defaultGuard}");
        $this->info("Guard driver: " . ($guardConfig['driver'] ?? 'not set'));
        $this->info("Guard provider: " . ($guardConfig['provider'] ?? 'not set'));

        $providerConfig = config("auth.providers." . ($guardConfig['provider'] ?? 'users'));
        $this->info("Provider driver: " . ($providerConfig['driver'] ?? 'not set'));
        $this->info("Provider model: " . ($providerConfig['model'] ?? 'not set'));

        // Test 7: Check User model configuration
        $this->info("\n7. User model check:");
        $userModel = $providerConfig['model'] ?? App\Models\User::class;
        $this->info("Using model: {$userModel}");

        if (class_exists($userModel)) {
            $this->info("✅ Model class exists");

            $testUser = new $userModel;
            $this->info("Table name: " . $testUser->getTable());
            $this->info("Auth identifier: " . $testUser->getAuthIdentifierName());

            // Check if model can find our user
            $foundUser = $userModel::where('email', $email)->first();
            if ($foundUser) {
                $this->info("✅ Model can find user");
                $this->info("User ID: {$foundUser->id}");
                $this->info("User email: {$foundUser->email}");
            } else {
                $this->error("❌ Model cannot find user");
            }
        } else {
            $this->error("❌ Model class does not exist");
        }

        // Test 8: Create a completely new test user
        $this->info("\n8. Creating fresh test user:");
        $testEmail = 'test.admin' . time() . '@example.com';
        $testPassword = 'TestPass123!';

        try {
            // Delete if exists
            User::where('email', $testEmail)->delete();

            // Create new
            $testUser = User::create([
                'name' => 'Test Admin',
                'email' => $testEmail,
                'password' => Hash::make($testPassword),
                'email_verified_at' => now(),
            ]);

            $this->info("✅ Created test user:");
            $this->info("  Email: {$testEmail}");
            $this->info("  Password: {$testPassword}");

            // Attach to companies
            DB::table('company_user')->insert([
                ['user_id' => $testUser->id, 'company_id' => 1, 'role' => 'super_admin', 'created_at' => now(), 'updated_at' => now()],
                ['user_id' => $testUser->id, 'company_id' => 2, 'role' => 'super_admin', 'created_at' => now(), 'updated_at' => now()],
            ]);
            $this->info("✅ Attached to companies");

            // Test authentication
            $testResult = Auth::attempt(['email' => $testEmail, 'password' => $testPassword]);
            $this->info("Test user authentication: " . ($testResult ? '✅ SUCCESS' : '❌ FAILED'));

            if ($testResult) {
                Auth::logout();
            }

            $this->info("\nTry logging in with this NEW user:");
            $this->info("  Email: {$testEmail}");
            $this->info("  Password: {$testPassword}");

        } catch (\Exception $e) {
            $this->error("Failed to create test user: " . $e->getMessage());
        }

        $this->info("\n==== Test Complete ====");

        $this->info("\nCurrent working credentials:");
        $this->info("Main admin: rob.thomas@empuls3.com / TestPassword123!");
        if (isset($testEmail)) {
            $this->info("Test admin: {$testEmail} / {$testPassword}");
        }

        return 0;
    }
}