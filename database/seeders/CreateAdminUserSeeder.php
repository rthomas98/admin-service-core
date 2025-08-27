<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@servicecore.local'],
            [
                'name' => 'Admin User',
                'email' => 'admin@servicecore.local',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $this->command->info('Admin user created: admin@servicecore.local / password123');
    }
}