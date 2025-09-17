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

            // Get the user ID
            $userId = DB::table('users')->where('email', 'rob.thomas@empuls3.com')->value('id');

            // Find or create companies and attach user
            $this->attachToCompanies($userId);

            // Verify the user was created
            $count = DB::table('users')->where('email', 'rob.thomas@empuls3.com')->count();
            $this->info("Verification: Found {$count} user(s) with this email");

        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function attachToCompanies($userId)
    {
        $this->info('Attaching user to companies...');

        // Find or create the two main companies
        $livTransportId = DB::table('companies')
            ->where('name', 'LIKE', '%LIV%Transport%')
            ->orWhere('slug', 'liv-transport')
            ->value('id');

        $rawDisposalId = DB::table('companies')
            ->where('name', 'LIKE', '%RAW%Disposal%')
            ->orWhere('slug', 'raw-disposal')
            ->value('id');

        // If companies don't exist, create them
        if (!$livTransportId) {
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
            $this->info('Created LIV Transport company');
        }

        if (!$rawDisposalId) {
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
            $this->info('Created RAW Disposal company');
        }

        // Attach user to both companies
        if ($userId && $livTransportId) {
            $exists = DB::table('company_user')
                ->where('user_id', $userId)
                ->where('company_id', $livTransportId)
                ->exists();

            if (!$exists) {
                DB::table('company_user')->insert([
                    'user_id' => $userId,
                    'company_id' => $livTransportId,
                    'role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info('✅ Attached to LIV Transport');
            }
        }

        if ($userId && $rawDisposalId) {
            $exists = DB::table('company_user')
                ->where('user_id', $userId)
                ->where('company_id', $rawDisposalId)
                ->exists();

            if (!$exists) {
                DB::table('company_user')->insert([
                    'user_id' => $userId,
                    'company_id' => $rawDisposalId,
                    'role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->info('✅ Attached to RAW Disposal');
            }
        }
    }
}
