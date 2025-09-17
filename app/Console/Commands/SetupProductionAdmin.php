<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupProductionAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-production-admin {--force : Force create with hardcoded password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Non-interactive setup for production super admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up production super admin...');

        // Use hardcoded password for initial setup if --force flag is used
        if ($this->option('force')) {
            $password = 'G00dBoySpot!!1013';
            $this->warn('⚠️  Using hardcoded password for initial setup.');
            $this->warn('⚠️  IMPORTANT: Change this password immediately after first login!');
        } else {
            // Try to get from environment
            $password = env('SUPER_ADMIN_PASSWORD');

            if (!$password) {
                $this->error('❌ SUPER_ADMIN_PASSWORD not set in environment variables.');
                $this->info('');
                $this->info('Option 1: Set SUPER_ADMIN_PASSWORD in Laravel Cloud environment variables');
                $this->info('Option 2: Run with --force flag to use default password (CHANGE IT IMMEDIATELY)');
                $this->info('');
                $this->info('Example: php artisan app:setup-production-admin --force');
                return 1;
            }
        }

        // Create or update the super admin
        $user = User::updateOrCreate(
            ['email' => 'rob.thomas@empuls3.com'],
            [
                'name' => 'Rob Thomas',
                'email' => 'rob.thomas@empuls3.com',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info('✅ Super admin account created successfully!');
        } else {
            $this->info('✅ Super admin account password updated!');
        }

        $this->info('');
        $this->info('Account Details:');
        $this->info('Email: rob.thomas@empuls3.com');
        $this->info('Name: Rob Thomas');

        if ($this->option('force')) {
            $this->warn('⚠️  SECURITY WARNING: Change password immediately after login!');
        }

        return 0;
    }
}
