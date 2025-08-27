<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get companies
        $livTransport = Company::where('slug', 'liv-transport')->first();
        $rawDisposal = Company::where('slug', 'raw-disposal')->first();
        
        if (!$livTransport || !$rawDisposal) {
            $this->command->error('Companies not found! Run CompanySeeder first.');
            return;
        }
        
        // Create admin user (has access to both companies)
        $admin = User::updateOrCreate(
            ['email' => 'admin@servicecore.local'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Attach admin to both companies
        $admin->companies()->syncWithoutDetaching([
            $livTransport->id => ['role' => 'admin'],
            $rawDisposal->id => ['role' => 'admin'],
        ]);
        
        $this->command->info('✅ Admin user created: admin@servicecore.local / password123');
        
        // Create LIV Transport specific users
        $livManager = User::updateOrCreate(
            ['email' => 'manager@livtransport.com'],
            [
                'name' => 'LIV Manager',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $livManager->companies()->syncWithoutDetaching([
            $livTransport->id => ['role' => 'manager'],
        ]);
        
        $this->command->info('✅ LIV Transport manager created: manager@livtransport.com / password123');
        
        $livEmployee = User::updateOrCreate(
            ['email' => 'employee@livtransport.com'],
            [
                'name' => 'LIV Employee',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $livEmployee->companies()->syncWithoutDetaching([
            $livTransport->id => ['role' => 'employee'],
        ]);
        
        $this->command->info('✅ LIV Transport employee created: employee@livtransport.com / password123');
        
        // Create RAW Disposal specific users
        $rawManager = User::updateOrCreate(
            ['email' => 'manager@rawdisposal.com'],
            [
                'name' => 'RAW Manager',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $rawManager->companies()->syncWithoutDetaching([
            $rawDisposal->id => ['role' => 'manager'],
        ]);
        
        $this->command->info('✅ RAW Disposal manager created: manager@rawdisposal.com / password123');
        
        $rawEmployee = User::updateOrCreate(
            ['email' => 'employee@rawdisposal.com'],
            [
                'name' => 'RAW Employee',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $rawEmployee->companies()->syncWithoutDetaching([
            $rawDisposal->id => ['role' => 'employee'],
        ]);
        
        $this->command->info('✅ RAW Disposal employee created: employee@rawdisposal.com / password123');
        
        // Create dispatcher user for RAW Disposal
        $rawDispatcher = User::updateOrCreate(
            ['email' => 'dispatcher@rawdisposal.com'],
            [
                'name' => 'RAW Dispatcher',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $rawDispatcher->companies()->syncWithoutDetaching([
            $rawDisposal->id => ['role' => 'employee'],
        ]);
        
        $this->command->info('✅ RAW Disposal dispatcher created: dispatcher@rawdisposal.com / password123');
        
        // Create driver user for LIV Transport
        $livDriver = User::updateOrCreate(
            ['email' => 'driver@livtransport.com'],
            [
                'name' => 'LIV Driver',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $livDriver->companies()->syncWithoutDetaching([
            $livTransport->id => ['role' => 'employee'],
        ]);
        
        $this->command->info('✅ LIV Transport driver created: driver@livtransport.com / password123');
        
        // Create billing user (access to both companies for invoicing)
        $billing = User::updateOrCreate(
            ['email' => 'billing@servicecore.local'],
            [
                'name' => 'Billing Department',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        
        $billing->companies()->syncWithoutDetaching([
            $livTransport->id => ['role' => 'manager'],
            $rawDisposal->id => ['role' => 'manager'],
        ]);
        
        $this->command->info('✅ Billing user created: billing@servicecore.local / password123');
        
        $this->command->info("\n📋 User Summary:");
        $this->command->info("================");
        $this->command->info("Admin (Both Companies):");
        $this->command->info("  - admin@servicecore.local / password123");
        $this->command->info("  - billing@servicecore.local / password123");
        $this->command->info("\nLIV Transport Users:");
        $this->command->info("  - manager@livtransport.com / password123");
        $this->command->info("  - employee@livtransport.com / password123");
        $this->command->info("  - driver@livtransport.com / password123");
        $this->command->info("\nRAW Disposal Users:");
        $this->command->info("  - manager@rawdisposal.com / password123");
        $this->command->info("  - employee@rawdisposal.com / password123");
        $this->command->info("  - dispatcher@rawdisposal.com / password123");
        
        $totalUsers = User::count();
        $this->command->info("\n✅ Total users in system: {$totalUsers}");
    }
}