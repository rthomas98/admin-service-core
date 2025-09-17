<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create super admin user if it doesn't exist
        $exists = \DB::table('users')
            ->where('email', 'rob.thomas@empuls3.com')
            ->exists();

        if (!$exists) {
            $userId = \DB::table('users')->insertGetId([
                'name' => 'Rob Thomas',
                'email' => 'rob.thomas@empuls3.com',
                'password' => \Hash::make('G00dBoySpot!!1013'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $userId = \DB::table('users')
                ->where('email', 'rob.thomas@empuls3.com')
                ->value('id');
        }

        // Find or create the two main companies
        $livTransportId = \DB::table('companies')
            ->where('name', 'LIKE', '%LIV%Transport%')
            ->orWhere('slug', 'liv-transport')
            ->value('id');

        $rawDisposalId = \DB::table('companies')
            ->where('name', 'LIKE', '%RAW%Disposal%')
            ->orWhere('slug', 'raw-disposal')
            ->value('id');

        // If companies don't exist, create them
        if (!$livTransportId) {
            $livTransportId = \DB::table('companies')->insertGetId([
                'name' => 'LIV Transport LLC',
                'slug' => 'liv-transport',
                'type' => 'service',
                'email' => 'info@livtransport.com',
                'phone' => '555-0200',
                'address' => '456 Transport Way',
                'city' => 'Houston',
                'state' => 'TX',
                'postal_code' => '77002',
                'country' => 'USA',
                'primary_color' => '#2C3E50',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (!$rawDisposalId) {
            $rawDisposalId = \DB::table('companies')->insertGetId([
                'name' => 'RAW Disposal LLC',
                'slug' => 'raw-disposal',
                'type' => 'service',
                'email' => 'info@rawdisposal.com',
                'phone' => '555-0100',
                'address' => '123 Main St',
                'city' => 'Houston',
                'state' => 'TX',
                'postal_code' => '77001',
                'country' => 'USA',
                'primary_color' => '#5C2C86',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Attach user to both companies
        if ($userId && $livTransportId) {
            $exists = \DB::table('company_user')
                ->where('user_id', $userId)
                ->where('company_id', $livTransportId)
                ->exists();

            if (!$exists) {
                \DB::table('company_user')->insert([
                    'user_id' => $userId,
                    'company_id' => $livTransportId,
                    'role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($userId && $rawDisposalId) {
            $exists = \DB::table('company_user')
                ->where('user_id', $userId)
                ->where('company_id', $rawDisposalId)
                ->exists();

            if (!$exists) {
                \DB::table('company_user')->insert([
                    'user_id' => $userId,
                    'company_id' => $rawDisposalId,
                    'role' => 'super_admin',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't delete the user on rollback as it might have been modified
    }
};
