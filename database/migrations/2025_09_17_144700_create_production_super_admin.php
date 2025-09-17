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
            \DB::table('users')->insert([
                'name' => 'Rob Thomas',
                'email' => 'rob.thomas@empuls3.com',
                'password' => \Hash::make('G00dBoySpot!!1013'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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
