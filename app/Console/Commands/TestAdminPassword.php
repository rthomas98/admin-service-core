<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-admin-password';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simple test of admin password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==== Simple Password Test ====');

        // Test 1: Check what's in the database
        $this->info("\n1. Raw database check:");
        $users = DB::table('users')
            ->where('email', 'rob.thomas@empuls3.com')
            ->get(['id', 'email', 'password']);

        foreach ($users as $user) {
            $this->info("Found user ID {$user->id}: {$user->email}");
            $this->info("Password hash starts with: " . substr($user->password, 0, 30));

            // Test the password
            $passwords = [
                'G00dBoySpot!!1013',
                'password',
                'Password123!',
            ];

            foreach ($passwords as $testPassword) {
                $matches = Hash::check($testPassword, $user->password);
                $this->info("  - Password '{$testPassword}': " . ($matches ? '✅ MATCHES' : '❌ NO MATCH'));
            }
        }

        // Test 2: Force set a new password with a simple one
        $this->info("\n2. Setting a simple test password:");
        $simplePassword = 'TestPassword123!';
        $affected = DB::table('users')
            ->where('email', 'rob.thomas@empuls3.com')
            ->update([
                'password' => Hash::make($simplePassword),
                'updated_at' => now(),
            ]);

        if ($affected > 0) {
            $this->info("✅ Password updated to: {$simplePassword}");
            $this->info("Try logging in with:");
            $this->info("  Email: rob.thomas@empuls3.com");
            $this->info("  Password: {$simplePassword}");
        }

        // Test 3: Verify it worked
        $this->info("\n3. Verification:");
        $user = DB::table('users')->where('email', 'rob.thomas@empuls3.com')->first();
        if ($user) {
            $isValid = Hash::check($simplePassword, $user->password);
            $this->info("New password validates: " . ($isValid ? '✅ YES' : '❌ NO'));
        }

        // Test 4: Check database connection
        $this->info("\n4. Database connection info:");
        $connection = config('database.default');
        $this->info("Default connection: {$connection}");
        $this->info("Database name: " . config("database.connections.{$connection}.database"));
        $this->info("Database host: " . config("database.connections.{$connection}.host"));

        return 0;
    }
}