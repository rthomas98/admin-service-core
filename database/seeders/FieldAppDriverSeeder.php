<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Driver;
use App\Enums\DriverStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class FieldAppDriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get companies
        $livTransport = Company::where('slug', 'liv-transport')->first();
        $rawDisposal = Company::where('slug', 'raw-disposal')->first();
        
        if (!$livTransport) {
            $this->command->error('LIV Transport company not found! Run CompanySeeder first.');
            return;
        }
        
        if (!$rawDisposal) {
            $this->command->error('RAW Disposal company not found! Run CompanySeeder first.');
            return;
        }
        
        $this->command->info('Creating Field App test drivers...');
        
        // Create test driver for LIV Transport
        $livFieldDriver = User::updateOrCreate(
            ['email' => 'john.smith@livtransport.com'],
            [
                'name' => 'John Smith',
                'password' => Hash::make('driver123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Attach to LIV Transport company with driver role
        $livFieldDriver->companies()->syncWithoutDetaching([
            $livTransport->id => ['role' => 'driver'],
        ]);
        
        // Create Driver record for LIV Transport
        $livDriver = Driver::updateOrCreate(
            [
                'company_id' => $livTransport->id,
                'user_id' => $livFieldDriver->id,
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@livtransport.com',
                'phone' => '(312) 555-0001',
                'license_number' => 'IL-CDL-123456',
                'license_class' => 'CDL-A',
                'license_expiry_date' => Carbon::now()->addYears(2),
                'vehicle_type' => 'truck',
                'vehicle_registration' => 'IL-12345',
                'vehicle_make' => 'Freightliner',
                'vehicle_model' => 'Cascadia',
                'vehicle_year' => 2022,
                'service_areas' => json_encode(['Chicago', 'Cook County', 'DuPage County']),
                'can_lift_heavy' => true,
                'has_truck_crane' => true,
                'hourly_rate' => 35.00,
                'shift_start_time' => '06:00:00',
                'shift_end_time' => '14:00:00',
                'available_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'status' => 'active',
                'hired_date' => Carbon::now()->subYears(2),
                'notes' => 'Experienced driver with excellent safety record. Field app test account.',
            ]
        );
        
        $this->command->info('✅ LIV Transport field driver created:');
        $this->command->info('   Email: john.smith@livtransport.com');
        $this->command->info('   Password: driver123');
        $this->command->info('   License: IL-CDL-123456 (CDL-A)');
        
        // Create test driver for RAW Disposal
        $rawFieldDriver = User::updateOrCreate(
            ['email' => 'mike.johnson@rawdisposal.com'],
            [
                'name' => 'Mike Johnson',
                'password' => Hash::make('driver123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Attach to RAW Disposal company with driver role
        $rawFieldDriver->companies()->syncWithoutDetaching([
            $rawDisposal->id => ['role' => 'driver'],
        ]);
        
        // Create Driver record for RAW Disposal
        $rawDriver = Driver::updateOrCreate(
            [
                'company_id' => $rawDisposal->id,
                'user_id' => $rawFieldDriver->id,
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@rawdisposal.com',
                'phone' => '(708) 555-0002',
                'license_number' => 'IL-CDL-789012',
                'license_class' => 'CDL-B',
                'license_expiry_date' => Carbon::now()->addYears(3),
                'vehicle_type' => 'truck',
                'vehicle_registration' => 'IL-67890',
                'vehicle_make' => 'Mack',
                'vehicle_model' => 'TerraPro',
                'vehicle_year' => 2021,
                'service_areas' => json_encode(['South Chicago', 'Oak Lawn', 'Orland Park']),
                'can_lift_heavy' => true,
                'has_truck_crane' => false,
                'hourly_rate' => 32.00,
                'shift_start_time' => '05:00:00',
                'shift_end_time' => '13:00:00',
                'available_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']),
                'status' => 'active',
                'hired_date' => Carbon::now()->subYears(1)->subMonths(6),
                'notes' => 'Waste management specialist. Field app test account.',
            ]
        );
        
        $this->command->info('✅ RAW Disposal field driver created:');
        $this->command->info('   Email: mike.johnson@rawdisposal.com');
        $this->command->info('   Password: driver123');
        $this->command->info('   License: IL-CDL-789012 (CDL-B)');
        
        // Create a driver with access to both companies
        $multiCompanyDriver = User::updateOrCreate(
            ['email' => 'alex.williams@servicecore.com'],
            [
                'name' => 'Alex Williams',
                'password' => Hash::make('driver123'),
                'email_verified_at' => now(),
            ]
        );
        
        // Attach to both companies with driver role
        $multiCompanyDriver->companies()->syncWithoutDetaching([
            $livTransport->id => ['role' => 'driver'],
            $rawDisposal->id => ['role' => 'driver'],
        ]);
        
        // Create Driver record for LIV Transport (primary)
        $multiDriver = Driver::updateOrCreate(
            [
                'company_id' => $livTransport->id,
                'user_id' => $multiCompanyDriver->id,
            ],
            [
                'first_name' => 'Alex',
                'last_name' => 'Williams',
                'email' => 'alex.williams@servicecore.com',
                'phone' => '(847) 555-0003',
                'license_number' => 'IL-CDL-345678',
                'license_class' => 'CDL-A',
                'license_expiry_date' => Carbon::now()->addYears(4),
                'vehicle_type' => 'truck',
                'vehicle_registration' => 'IL-34567',
                'vehicle_make' => 'Kenworth',
                'vehicle_model' => 'T680',
                'vehicle_year' => 2023,
                'service_areas' => json_encode(['Chicago', 'Evanston', 'Skokie', 'All Counties']),
                'can_lift_heavy' => true,
                'has_truck_crane' => true,
                'hourly_rate' => 40.00,
                'shift_start_time' => '07:00:00',
                'shift_end_time' => '15:00:00',
                'available_days' => json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
                'status' => 'active',
                'hired_date' => Carbon::now()->subYears(5),
                'notes' => 'Cross-trained driver for both companies. Field app test account.',
            ]
        );
        
        $this->command->info('✅ Multi-company field driver created:');
        $this->command->info('   Email: alex.williams@servicecore.com');
        $this->command->info('   Password: driver123');
        $this->command->info('   Access: Both LIV Transport and RAW Disposal');
        $this->command->info('   License: IL-CDL-345678 (CDL-A)');
        
        $this->command->info("\n" . str_repeat('=', 60));
        $this->command->info('📱 FIELD APP LOGIN CREDENTIALS');
        $this->command->info(str_repeat('=', 60));
        $this->command->info("\n🚛 LIV TRANSPORT DRIVER:");
        $this->command->info('   Email: john.smith@livtransport.com');
        $this->command->info('   Password: driver123');
        $this->command->info('   Company: LIV Transport');
        
        $this->command->info("\n♻️ RAW DISPOSAL DRIVER:");
        $this->command->info('   Email: mike.johnson@rawdisposal.com');
        $this->command->info('   Password: driver123');
        $this->command->info('   Company: RAW Disposal');
        
        $this->command->info("\n🔄 MULTI-COMPANY DRIVER:");
        $this->command->info('   Email: alex.williams@servicecore.com');
        $this->command->info('   Password: driver123');
        $this->command->info('   Company: Choose between LIV Transport or RAW Disposal');
        
        $this->command->info("\n" . str_repeat('=', 60));
        $this->command->info('✅ Field app driver accounts created successfully!');
    }
}