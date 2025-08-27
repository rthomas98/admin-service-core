<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ServiceOrder;
use App\Models\Pricing;
use App\Models\Driver;
use App\Models\ServiceArea;
use Illuminate\Console\Command;

class TestRawDisposalSetup extends Command
{
    protected $signature = 'test:raw-disposal';
    protected $description = 'Test RAW Disposal models and relationships';

    public function handle(): int
    {
        $this->info('Testing RAW Disposal Setup');
        $this->info('==========================');
        
        // Get RAW Disposal company
        $rawDisposal = Company::where('slug', 'raw-disposal')->first();
        
        if (!$rawDisposal) {
            $this->error('RAW Disposal company not found!');
            return Command::FAILURE;
        }
        
        $this->info("\n✅ RAW Disposal Company Found");
        $this->info("Company: {$rawDisposal->name}");
        $this->info("Type: {$rawDisposal->type}");
        
        // Check models
        $this->info("\n📊 Model Statistics:");
        
        $models = [
            'Customers' => Customer::class,
            'Equipment' => Equipment::class,
            'Service Orders' => ServiceOrder::class,
            'Pricing' => Pricing::class,
            'Drivers' => Driver::class,
            'Service Areas' => ServiceArea::class,
        ];
        
        foreach ($models as $name => $model) {
            $count = $model::where('company_id', $rawDisposal->id)->count();
            $this->line("- {$name}: {$count}");
        }
        
        // Test creating sample data
        $this->info("\n🔧 Creating Sample Data...");
        
        // Create sample equipment
        $equipment = Equipment::create([
            'company_id' => $rawDisposal->id,
            'type' => 'dumpster',
            'size' => '20-yard',
            'unit_number' => 'DUMP-' . rand(1000, 9999),
            'status' => 'available',
            'condition' => 'good',
            'current_location' => 'RAW Disposal Yard',
            'purchase_date' => now()->subMonths(6),
            'purchase_price' => 5000.00,
            'color' => 'Purple',
        ]);
        
        $this->info("✅ Created Equipment: {$equipment->unit_number}");
        
        // Create sample pricing
        $pricing = Pricing::create([
            'company_id' => $rawDisposal->id,
            'equipment_type' => 'dumpster',
            'size' => '20-yard',
            'daily_rate' => 50.00,
            'weekly_rate' => 250.00,
            'monthly_rate' => 800.00,
            'delivery_fee' => 75.00,
            'pickup_fee' => 75.00,
            'is_active' => true,
        ]);
        
        $this->info("✅ Created Pricing for 20-yard dumpster");
        
        // Create sample driver
        $driver = Driver::create([
            'company_id' => $rawDisposal->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@rawdisposal.com',
            'phone' => '504-555-0123',
            'license_number' => 'LA' . rand(100000, 999999),
            'license_class' => 'CDL-A',
            'license_expiry_date' => now()->addYear(),
            'vehicle_type' => 'Box Truck',
            'vehicle_registration' => 'ABC123',
            'vehicle_make' => 'Isuzu',
            'vehicle_model' => 'NPR',
            'vehicle_year' => 2022,
            'service_areas' => ['Orleans Parish', 'Jefferson Parish'],
            'can_lift_heavy' => true,
            'has_truck_crane' => true,
            'hourly_rate' => 25.00,
            'shift_start_time' => '07:00:00',
            'shift_end_time' => '17:00:00',
            'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'status' => 'active',
            'hired_date' => now()->subMonths(6),
        ]);
        
        $this->info("✅ Created Driver: {$driver->first_name} {$driver->last_name}");
        
        // Create sample service area
        $serviceArea = ServiceArea::create([
            'company_id' => $rawDisposal->id,
            'name' => 'Greater New Orleans',
            'zip_codes' => ['70112', '70113', '70114', '70115', '70116'],
            'parishes' => ['Orleans Parish', 'Jefferson Parish'],
            'delivery_surcharge' => 0,
            'is_active' => true,
        ]);
        
        $this->info("✅ Created Service Area: {$serviceArea->name}");
        
        // Get a customer to create an order
        $customer = Customer::where('company_id', $rawDisposal->id)->first();
        
        if ($customer) {
            // Create sample service order
            $order = ServiceOrder::create([
                'company_id' => $rawDisposal->id,
                'customer_id' => $customer->id,
                'order_number' => 'SO-' . date('Ymd') . '-' . rand(1000, 9999),
                'service_type' => 'rental',
                'status' => 'pending',
                'delivery_date' => now()->addDays(2),
                'pickup_date' => now()->addDays(9),
                'delivery_address' => $customer->address,
                'delivery_city' => $customer->city,
                'delivery_state' => $customer->state,
                'delivery_zip' => $customer->zip,
                'special_instructions' => 'Place dumpster in driveway',
                'total_amount' => 400.00,
            ]);
            
            $this->info("✅ Created Service Order: {$order->order_number}");
            
            // Attach equipment to order
            $order->equipment()->attach($equipment->id, [
                'quantity' => 1,
                'daily_rate' => $pricing->daily_rate
            ]);
            
            $this->info("✅ Attached equipment to order");
        }
        
        $this->info("\n🎉 RAW Disposal Setup Test Complete!");
        $this->info("\nYou can now:");
        $this->info("1. Login at: http://admin-service-core.test/admin/login");
        $this->info("2. Use credentials: admin@servicecore.local / password123");
        $this->info("3. Switch to RAW Disposal company");
        $this->info("4. Access all RAW Disposal resources in the admin panel");
        
        return Command::SUCCESS;
    }
}