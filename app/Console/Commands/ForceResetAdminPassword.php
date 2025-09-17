<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForceResetAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:force-reset-admin-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force reset admin password and clear all caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==== Force Reset Admin Password ====');

        $email = 'rob.thomas@empuls3.com';
        $password = 'G00dBoySpot!!1013';

        // Method 1: Update via Eloquent Model
        $this->info("\n1. Updating password via Eloquent Model...");
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
            $this->info("✅ Password updated via Model (User ID: {$user->id})");
        } else {
            $this->error("❌ User not found via Model");
        }

        // Method 2: Direct database update
        $this->info("\n2. Updating password via direct database query...");
        $hashedPassword = Hash::make($password);
        $affected = DB::table('users')
            ->where('email', $email)
            ->update([
                'password' => $hashedPassword,
                'updated_at' => now(),
            ]);

        if ($affected > 0) {
            $this->info("✅ Password updated directly in database");
        } else {
            $this->error("❌ No rows affected in direct update");
        }

        // Verify the password hash
        $this->info("\n3. Verifying password hash...");
        $dbUser = DB::table('users')->where('email', $email)->first();
        if ($dbUser) {
            $this->info("Current password hash: " . substr($dbUser->password, 0, 30) . "...");
            $isValid = Hash::check($password, $dbUser->password);
            $this->info("Password 'G00dBoySpot!!1013' validates: " . ($isValid ? '✅ YES' : '❌ NO'));

            // Try creating a new hash and comparing
            $newHash = Hash::make($password);
            $this->info("New hash would be: " . substr($newHash, 0, 30) . "...");
            $this->info("Hashes are different (expected): " . ($newHash !== $dbUser->password ? 'YES' : 'NO'));
        }

        // Clear ALL caches
        $this->info("\n4. Clearing ALL caches...");

        // Application cache
        $this->call('cache:clear');
        $this->info("✅ Application cache cleared");

        // Configuration cache
        $this->call('config:clear');
        $this->info("✅ Configuration cache cleared");

        // Route cache
        $this->call('route:clear');
        $this->info("✅ Route cache cleared");

        // View cache
        $this->call('view:clear');
        $this->info("✅ View cache cleared");

        // Try to clear opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->info("✅ OPcache reset");
        }

        // Optimize clear (Laravel's full cache clear)
        $this->call('optimize:clear');
        $this->info("✅ All Laravel caches cleared");

        // Test authentication one more time
        $this->info("\n5. Final authentication test...");
        $attempt = \Auth::attempt(['email' => $email, 'password' => $password]);

        if ($attempt) {
            $this->info("✅ Authentication successful!");
            \Auth::logout();
        } else {
            $this->error("❌ Authentication still failing");

            // Try to understand why
            $user = User::where('email', $email)->first();
            if ($user) {
                $this->info("\nDebugging info:");
                $this->info("- User exists: YES");
                $this->info("- User ID: {$user->id}");
                $this->info("- Email verified: " . ($user->email_verified_at ? 'YES' : 'NO'));
                $this->info("- Companies: " . $user->companies()->count());
                $this->info("- Password check: " . (Hash::check($password, $user->password) ? 'VALID' : 'INVALID'));
            }
        }

        $this->info("\n==== Complete ====");
        $this->info("\nLogin credentials:");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        $this->info("\nIf login still fails, try:");
        $this->info("1. Restart the application server");
        $this->info("2. Clear browser cache and cookies");
        $this->info("3. Try incognito/private browsing mode");

        return 0;
    }
}