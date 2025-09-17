<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ForceCreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:force-create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force create admin user with direct database insert';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Force creating admin user...');

        try {
            // Delete existing user if it exists
            DB::table('users')->where('email', 'rob.thomas@empuls3.com')->delete();

            // Insert new user directly
            DB::table('users')->insert([
                'name' => 'Rob Thomas',
                'email' => 'rob.thomas@empuls3.com',
                'password' => Hash::make('G00dBoySpot!!1013'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info('✅ Admin user created successfully!');
            $this->info('Email: rob.thomas@empuls3.com');
            $this->info('Password: G00dBoySpot!!1013');

            // Verify the user was created
            $count = DB::table('users')->where('email', 'rob.thomas@empuls3.com')->count();
            $this->info("Verification: Found {$count} user(s) with this email");

        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
