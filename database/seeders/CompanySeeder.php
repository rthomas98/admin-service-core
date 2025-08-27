<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create LIV Transport company
        $livTransport = Company::updateOrCreate(
            ['slug' => 'liv-transport'],
            [
                'name' => 'LIV Transport LLC',
                'slug' => 'liv-transport',
                'type' => 'transport',
                'email' => 'livtransportllc@gmail.com',
                'phone' => '504-877-2670',
                'address' => '5881 River Rd, Avondale, LA 70094-2753',
                'website' => 'https://liv-transport.test',
                'primary_color' => '#5C2C86',
                'settings' => [
                    'services' => ['freight_hauling', 'roll_off_containers', 'equipment_transport'],
                    'dbe_certified' => true,
                ],
                'is_active' => true,
            ]
        );
        
        // Create RAW Disposal company
        $rawDisposal = Company::updateOrCreate(
            ['slug' => 'raw-disposal'],
            [
                'name' => 'RAW Disposal LLC',
                'slug' => 'raw-disposal',
                'type' => 'disposal',
                'email' => 'info@rawdisposal.com',
                'phone' => '504-555-0100',
                'address' => 'New Orleans, LA',
                'website' => 'https://raw-disposal.test',
                'primary_color' => '#2563EB',
                'settings' => [
                    'services' => ['roll_off_dumpsters', 'portable_toilets', 'handwash_stations', 'holding_tanks', 'water_tanks'],
                ],
                'is_active' => true,
            ]
        );
        
        $this->command->info('Companies created:');
        $this->command->info('- LIV Transport LLC');
        $this->command->info('- RAW Disposal LLC');
        
        // Attach admin user to both companies if exists
        $adminUser = User::where('email', 'admin@servicecore.local')->first();
        
        if ($adminUser) {
            // Attach user to both companies as admin
            $adminUser->companies()->syncWithoutDetaching([
                $livTransport->id => ['role' => 'admin'],
                $rawDisposal->id => ['role' => 'admin'],
            ]);
            
            $this->command->info('Admin user attached to both companies as admin.');
        }
    }
}