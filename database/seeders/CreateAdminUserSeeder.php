<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create local admin for development
        if (app()->environment('local')) {
            User::updateOrCreate(
                ['email' => 'admin@servicecore.local'],
                [
                    'name' => 'Admin User',
                    'email' => 'admin@servicecore.local',
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
            $this->command->info('Local admin user created');
        }

        // Create production super admin - password must be set via environment variable
        $adminPassword = env('SUPER_ADMIN_PASSWORD');

        if (!$adminPassword && app()->environment('production')) {
            $this->command->error('SUPER_ADMIN_PASSWORD environment variable must be set for production');
            return;
        }

        User::updateOrCreate(
            ['email' => 'rob.thomas@empuls3.com'],
            [
                'name' => 'Rob Thomas',
                'email' => 'rob.thomas@empuls3.com',
                'password' => Hash::make($adminPassword ?: Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Super admin account created for: rob.thomas@empuls3.com');

        if (!$adminPassword) {
            $this->command->warn('⚠️  Password was randomly generated. Please set SUPER_ADMIN_PASSWORD in your .env file and re-run the seeder.');
        }
    }
}