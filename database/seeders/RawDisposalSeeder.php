<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\ServiceArea;
use App\Models\ServiceOrder;
use App\Models\ServiceSchedule;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Driver;
use App\Models\MaintenanceLog;
use App\Models\EmergencyService;
use App\Models\DeliverySchedule;
use App\Models\Payment;
use App\Models\Pricing;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RawDisposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or get RAW Disposal company
        $rawDisposal = Company::where('slug', 'raw-disposal')->first();
        
        if (!$rawDisposal) {
            $rawDisposal = Company::factory()->rawDisposal()->create();
        } else {
            $this->command->info('RAW Disposal company already exists, using existing company.');
        }
        
        // Create or get admin user for RAW Disposal
        $admin = User::where('email', 'admin@rawdisposal.com')->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'RAW Admin',
                'email' => 'admin@rawdisposal.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
        }
        
        // Attach admin to company if not already attached
        if (!$admin->companies()->where('company_id', $rawDisposal->id)->exists()) {
            $admin->companies()->attach($rawDisposal->id, ['role' => 'admin']);
        }
        
        // Create service areas for RAW Disposal
        $orleansArea = ServiceArea::factory()
            ->orleansParish()
            ->create(['company_id' => $rawDisposal->id]);
            
        $jeffersonArea = ServiceArea::factory()
            ->jeffersonParish()
            ->create(['company_id' => $rawDisposal->id]);
            
        ServiceArea::factory()
            ->count(3)
            ->create(['company_id' => $rawDisposal->id]);
        
        // Create drivers/technicians for RAW Disposal
        $drivers = [];
        for ($i = 0; $i < 5; $i++) {
            $driverUser = User::create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            $driverUser->companies()->attach($rawDisposal->id, ['role' => 'driver']);
            
            $driver = Driver::create([
                'company_id' => $rawDisposal->id,
                'user_id' => $driverUser->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => $driverUser->email,
                'phone' => fake()->phoneNumber(),
                'license_number' => strtoupper(fake()->bothify('??######')),
                'license_class' => 'CDL-A',
                'license_expiry_date' => fake()->dateTimeBetween('+6 months', '+3 years'),
                'vehicle_type' => 'Roll-off Truck',
                'vehicle_registration' => strtoupper(fake()->bothify('???-####')),
                'vehicle_make' => fake()->randomElement(['Mack', 'Kenworth', 'Peterbilt', 'Freightliner']),
                'vehicle_model' => fake()->randomElement(['CH613', 'T800', '379', 'Cascadia']),
                'vehicle_year' => fake()->numberBetween(2018, 2024),
                'service_areas' => [$orleansArea->id, $jeffersonArea->id],
                'can_lift_heavy' => true,
                'has_truck_crane' => fake()->boolean(60),
                'hourly_rate' => fake()->randomFloat(2, 20, 35),
                'shift_start_time' => '06:00:00',
                'shift_end_time' => '16:00:00',
                'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'status' => 'active',
                'hired_date' => fake()->dateTimeBetween('-5 years', '-6 months'),
            ]);
            
            $drivers[] = $driver;
        }
        
        // Create customers for RAW Disposal
        $residentialCustomers = Customer::factory()
            ->count(30)
            ->residential()
            ->active()
            ->create(['company_id' => $rawDisposal->id]);
            
        $commercialCustomers = Customer::factory()
            ->count(20)
            ->commercial()
            ->active()
            ->create(['company_id' => $rawDisposal->id]);
            
        $allCustomers = $residentialCustomers->concat($commercialCustomers);
        
        // Create equipment for RAW Disposal
        $dumpsters10 = Equipment::factory()
            ->count(15)
            ->dumpster(10)
            ->create(['company_id' => $rawDisposal->id]);
            
        $dumpsters20 = Equipment::factory()
            ->count(20)
            ->dumpster(20)
            ->create(['company_id' => $rawDisposal->id]);
            
        $dumpsters30 = Equipment::factory()
            ->count(10)
            ->dumpster(30)
            ->create(['company_id' => $rawDisposal->id]);
            
        $dumpsters40 = Equipment::factory()
            ->count(5)
            ->dumpster(40)
            ->create(['company_id' => $rawDisposal->id]);
            
        $portableToilets = Equipment::factory()
            ->count(25)
            ->state(['type' => 'portable_toilet'])
            ->create(['company_id' => $rawDisposal->id]);
            
        $allEquipment = collect()
            ->concat($dumpsters10)
            ->concat($dumpsters20)
            ->concat($dumpsters30)
            ->concat($dumpsters40)
            ->concat($portableToilets);
        
        // Create pricing for RAW Disposal equipment
        $this->command->info('Creating pricing plans...');
        
        // Standard residential pricing for dumpsters
        Pricing::factory()
            ->dumpster('10 Yard')
            ->residential()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Perfect for small home cleanouts and minor renovation projects. Ideal for 1-2 room cleanouts.',
            ]);
            
        Pricing::factory()
            ->dumpster('20 Yard')
            ->residential()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Great for medium-sized home projects, roof replacements, and garage cleanouts.',
            ]);
            
        Pricing::factory()
            ->dumpster('30 Yard')
            ->residential()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Ideal for large home renovations, estate cleanouts, and major construction projects.',
            ]);
            
        Pricing::factory()
            ->dumpster('40 Yard')
            ->residential()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Maximum capacity for large-scale demolition and commercial projects.',
            ]);
        
        // Commercial pricing for dumpsters
        Pricing::factory()
            ->dumpster('20 Yard')
            ->commercial()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Commercial rates with volume discounts for regular business customers.',
            ]);
            
        Pricing::factory()
            ->dumpster('30 Yard')
            ->commercial()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Commercial pricing for retail stores, restaurants, and office buildings.',
            ]);
            
        Pricing::factory()
            ->dumpster('40 Yard')
            ->commercial()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'High-volume commercial waste management for warehouses and industrial sites.',
            ]);
        
        // Construction pricing
        Pricing::factory()
            ->construction()
            ->count(3)
            ->create([
                'company_id' => $rawDisposal->id,
            ]);
        
        // Industrial pricing
        Pricing::factory()
            ->industrial()
            ->count(2)
            ->create([
                'company_id' => $rawDisposal->id,
            ]);
        
        // Portable toilet pricing
        Pricing::factory()
            ->portableToilet('Standard')
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Standard portable restroom with weekly service included.',
            ]);
            
        Pricing::factory()
            ->portableToilet('Deluxe')
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Deluxe model with hand sanitizer, mirror, and enhanced ventilation.',
            ]);
            
        Pricing::factory()
            ->portableToilet('ADA Compliant')
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Wheelchair accessible unit meeting all ADA requirements.',
            ]);
            
        Pricing::factory()
            ->portableToilet('VIP Trailer')
            ->premium()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Luxury restroom trailer with running water, climate control, and premium amenities.',
            ]);
        
        // Roll-off containers
        Pricing::factory()
            ->count(4)
            ->state(function (array $attributes) {
                $sizes = ['10 Yard', '20 Yard', '30 Yard', '40 Yard'];
                static $index = 0;
                return [
                    'equipment_type' => 'roll_off',
                    'size' => $sizes[$index++ % 4],
                ];
            })
            ->create([
                'company_id' => $rawDisposal->id,
            ]);
        
        // Compactors
        Pricing::factory()
            ->count(4)
            ->state(function (array $attributes) {
                $sizes = ['2 Yard', '4 Yard', '6 Yard', '8 Yard'];
                static $compactorIndex = 0;
                return [
                    'equipment_type' => 'compactor',
                    'size' => $sizes[$compactorIndex++ % 4],
                ];
            })
            ->commercial()
            ->create([
                'company_id' => $rawDisposal->id,
            ]);
        
        // Recycling bins
        Pricing::factory()
            ->count(3)
            ->state(function (array $attributes) {
                $sizes = ['Small', 'Medium', 'Large'];
                static $recyclingIndex = 0;
                return [
                    'equipment_type' => 'recycling_bin',
                    'size' => $sizes[$recyclingIndex++ % 3],
                ];
            })
            ->noFees()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Eco-friendly recycling solution with no additional fees.',
            ]);
        
        // Storage containers
        Pricing::factory()
            ->count(3)
            ->state(function (array $attributes) {
                $sizes = ['10 ft', '20 ft', '40 ft'];
                static $storageIndex = 0;
                return [
                    'equipment_type' => 'storage_container',
                    'size' => $sizes[$storageIndex++ % 3],
                ];
            })
            ->create([
                'company_id' => $rawDisposal->id,
            ]);
        
        // Create some special promotional pricing
        Pricing::factory()
            ->dumpster('15 Yard')
            ->budget()
            ->create([
                'company_id' => $rawDisposal->id,
                'description' => 'Budget-friendly option for cost-conscious customers.',
                'effective_from' => now(),
                'effective_until' => now()->addMonths(3),
            ]);
        
        // Create some upcoming pricing (future effective date)
        Pricing::factory()
            ->upcoming()
            ->count(2)
            ->create([
                'company_id' => $rawDisposal->id,
            ]);
        
        // Create some expired pricing for historical records
        Pricing::factory()
            ->expired()
            ->count(3)
            ->create([
                'company_id' => $rawDisposal->id,
            ]);
        
        // Deploy some equipment to customers
        $deployedEquipment = $allEquipment->random(30);
        foreach ($deployedEquipment as $equipment) {
            $customer = $allCustomers->random();
            $equipment->update([
                'status' => 'rented',
                'current_location' => $customer->address . ', ' . $customer->city . ', ' . $customer->state,
            ]);
        }
        
        // Create service orders for some customers
        foreach ($allCustomers->random(20) as $customer) {
            ServiceOrder::factory()
                ->scheduled()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
        }
        
        // Create completed service orders with invoices
        foreach ($allCustomers->random(15) as $customer) {
            $serviceOrder = ServiceOrder::factory()
                ->completed()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
            
            // Create invoice for completed service order
            Invoice::factory()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => $serviceOrder->id,
                ]);
        }
        
        // Create some paid invoices with service orders
        foreach ($allCustomers->random(10) as $customer) {
            $paidOrder = ServiceOrder::factory()
                ->completed()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
                
            Invoice::factory()
                ->paid()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => $paidOrder->id,
                ]);
        }
        
        // Create service schedules for today and upcoming days
        foreach ($allEquipment->random(15) as $equipment) {
            ServiceSchedule::factory()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'equipment_id' => $equipment->id,
                    'technician_id' => fake()->randomElement($drivers)->user_id,
                ]);
        }
        
        // Create some completed service schedules
        foreach ($allEquipment->random(20) as $equipment) {
            ServiceSchedule::factory()
                ->completed()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'equipment_id' => $equipment->id,
                    'technician_id' => fake()->randomElement($drivers)->user_id,
                ]);
        }
        
        // Create quotes
        $quotes = [];
        foreach ($allCustomers->random(15) as $customer) {
            $quote = Quote::factory()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                ]);
            $quotes[] = $quote;
        }
        
        // Set some quotes as accepted
        foreach (collect($quotes)->random(5) as $quote) {
            $quote->update([
                'status' => 'accepted',
                'accepted_date' => now()->subDays(rand(1, 5)),
            ]);
        }
        
        // Create maintenance logs for equipment
        foreach ($allEquipment->random(10) as $equipment) {
            MaintenanceLog::create([
                'company_id' => $rawDisposal->id,
                'equipment_id' => $equipment->id,
                'maintainable_type' => Equipment::class,
                'maintainable_id' => $equipment->id,
                'technician_id' => fake()->randomElement($drivers)->user_id,
                'service_type' => fake()->randomElement(['cleaning', 'repair', 'inspection', 'preventive']),
                'service_date' => fake()->dateTimeBetween('-2 months', 'now'),
                'service_cost' => fake()->randomFloat(2, 50, 500),
                'parts_cost' => fake()->randomFloat(2, 20, 200),
                'labor_cost' => fake()->randomFloat(2, 50, 200),
                'total_cost' => fake()->randomFloat(2, 120, 900),
                'work_performed' => fake()->sentence(),
                'condition_before' => fake()->randomElement(['excellent', 'good', 'fair', 'poor']),
                'condition_after' => fake()->randomElement(['excellent', 'good']),
                'requires_followup' => fake()->boolean(20),
                'next_service_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
            ]);
        }
        
        // Create some emergency service records
        foreach ($allCustomers->random(5) as $customer) {
            $emergencyRequest = fake()->dateTimeBetween('-1 month', 'now');
            $emergencyDispatch = Carbon::instance($emergencyRequest)->addMinutes(fake()->numberBetween(10, 30));
            $emergencyArrival = Carbon::instance($emergencyDispatch)->addMinutes(fake()->numberBetween(15, 45));
            $emergencyCompletion = Carbon::instance($emergencyArrival)->addMinutes(fake()->numberBetween(30, 120));
            
            EmergencyService::factory()
                ->completed()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'emergency_number' => EmergencyService::generateEmergencyNumber(),
                    'request_datetime' => $emergencyRequest,
                    'urgency_level' => fake()->randomElement(['high', 'critical']),
                    'emergency_type' => fake()->randomElement(['delivery', 'pickup', 'repair', 'replacement']),
                    'description' => fake()->sentence(),
                    'location_address' => $customer->address,
                    'location_city' => $customer->city,
                    'location_parish' => $customer->county,
                    'location_postal_code' => $customer->zip,
                    'contact_name' => $customer->first_name . ' ' . $customer->last_name,
                    'contact_phone' => $customer->phone,
                    'assigned_driver_id' => fake()->randomElement($drivers)->id,
                    'assigned_technician_id' => fake()->randomElement($drivers)->user_id,
                    'dispatched_datetime' => $emergencyDispatch,
                    'arrival_datetime' => $emergencyArrival,
                    'completion_datetime' => $emergencyCompletion,
                    'actual_response_minutes' => Carbon::instance($emergencyRequest)->diffInMinutes($emergencyArrival),
                    'status' => 'completed',
                    'completion_notes' => 'Emergency service completed successfully',
                    'emergency_surcharge' => fake()->randomFloat(2, 100, 300),
                    'total_cost' => fake()->randomFloat(2, 400, 1200),
                ]);
        }
        
        // Create delivery schedules for today
        $serviceOrdersToday = ServiceOrder::where('company_id', $rawDisposal->id)
            ->where('status', 'confirmed')
            ->take(5)
            ->get();
            
        foreach ($serviceOrdersToday as $order) {
            $equipment = $allEquipment->random();
            $driver = fake()->randomElement($drivers);
            
            DeliverySchedule::factory()
                ->today()
                ->delivery()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'service_order_id' => $order->id,
                    'equipment_id' => $equipment->id,
                    'driver_id' => $driver->id,
                    'delivery_address' => $order->delivery_address,
                    'delivery_city' => $order->delivery_city,
                    'delivery_parish' => $order->delivery_parish,
                    'delivery_postal_code' => $order->delivery_postal_code,
                ]);
        }
        
        // Create delivery schedules for tomorrow
        foreach ($allCustomers->random(8) as $customer) {
            $equipment = $allEquipment->random();
            $driver = fake()->randomElement($drivers);
            $serviceOrder = ServiceOrder::factory()
                ->scheduled()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
            
            DeliverySchedule::factory()
                ->tomorrow()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'service_order_id' => $serviceOrder->id,
                    'equipment_id' => $equipment->id,
                    'driver_id' => $driver->id,
                    'type' => fake()->randomElement(['delivery', 'pickup']),
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
        }
        
        // Create delivery schedules for this week
        foreach ($allCustomers->random(10) as $customer) {
            $equipment = $allEquipment->random();
            $driver = fake()->randomElement($drivers);
            $serviceOrder = ServiceOrder::factory()
                ->scheduled()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
            
            DeliverySchedule::factory()
                ->thisWeek()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'service_order_id' => $serviceOrder->id,
                    'equipment_id' => $equipment->id,
                    'driver_id' => $driver->id,
                    'type' => fake()->randomElement(['delivery', 'pickup', 'maintenance']),
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
        }
        
        // Create completed delivery schedules with history
        foreach ($allCustomers->random(20) as $customer) {
            $equipment = $allEquipment->random();
            $driver = fake()->randomElement($drivers);
            
            DeliverySchedule::factory()
                ->completed()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'service_order_id' => ServiceOrder::factory()->completed()->create([
                        'company_id' => $rawDisposal->id,
                        'customer_id' => $customer->id,
                    ])->id,
                    'equipment_id' => $equipment->id,
                    'driver_id' => $driver->id,
                    'type' => fake()->randomElement(['delivery', 'pickup']),
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
        }
        
        // Create some en route deliveries
        foreach ($allCustomers->random(3) as $customer) {
            $equipment = $allEquipment->random();
            $driver = fake()->randomElement($drivers);
            
            DeliverySchedule::factory()
                ->enRoute()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'service_order_id' => ServiceOrder::factory()->create([
                        'company_id' => $rawDisposal->id,
                        'customer_id' => $customer->id,
                        'status' => 'in_progress',
                    ])->id,
                    'equipment_id' => $equipment->id,
                    'driver_id' => $driver->id,
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
        }
        
        // Create some cancelled deliveries
        foreach ($allCustomers->random(2) as $customer) {
            $equipment = $allEquipment->random();
            $driver = fake()->randomElement($drivers);
            
            DeliverySchedule::factory()
                ->cancelled()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'service_order_id' => ServiceOrder::factory()->create([
                        'company_id' => $rawDisposal->id,
                        'customer_id' => $customer->id,
                        'status' => 'cancelled',
                    ])->id,
                    'equipment_id' => $equipment->id,
                    'driver_id' => $driver->id,
                    'delivery_address' => $customer->address,
                    'delivery_city' => $customer->city,
                    'delivery_parish' => $customer->county,
                    'delivery_postal_code' => $customer->zip,
                ]);
        }
        
        // Create Work Orders
        $this->command->info('Creating work orders...');
        
        // Work orders for today - mix of draft and in progress
        foreach ($allCustomers->random(8) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->today()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Work orders for this week
        foreach ($allCustomers->random(12) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->thisWeek()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Completed work orders - deliveries
        foreach ($allCustomers->random(15) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->completed()
                ->delivery()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Completed work orders - pickups
        foreach ($allCustomers->random(15) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->completed()
                ->pickup()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Completed work orders - swaps
        foreach ($allCustomers->random(8) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->completed()
                ->swap()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // In progress work orders
        foreach ($allCustomers->random(5) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->inProgress()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->create([
                    'company_id' => $rawDisposal->id,
                    'service_date' => today(),
                ]);
        }
        
        // Work orders with COD
        foreach ($allCustomers->random(5) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->withCOD()
                ->completed()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Construction waste work orders
        foreach ($commercialCustomers->random(10) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->constructionWaste()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->state(function (array $attributes) {
                    return [
                        'status' => fake()->randomElement(['draft', 'in_progress', 'completed']),
                    ];
                })
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Residential waste work orders
        foreach ($residentialCustomers->random(10) as $customer) {
            $driver = fake()->randomElement($drivers);
            
            WorkOrder::factory()
                ->residentialWaste()
                ->forCustomer($customer)
                ->forDriver($driver)
                ->state(function (array $attributes) {
                    return [
                        'status' => fake()->randomElement(['draft', 'completed']),
                    ];
                })
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Cancelled work orders
        foreach ($allCustomers->random(3) as $customer) {
            WorkOrder::factory()
                ->cancelled()
                ->forCustomer($customer)
                ->create([
                    'company_id' => $rawDisposal->id,
                ]);
        }
        
        // Create payments for invoices
        $this->command->info('Creating payments...');
        
        // Check if payments already exist
        $existingPayments = Payment::where('company_id', $rawDisposal->id)->count();
        if ($existingPayments > 0) {
            $this->command->info("Payments already exist for RAW Disposal. Skipping payment creation.");
            $this->command->info("Found {$existingPayments} existing payments.");
        } else {
            // Get all invoices
            $allInvoices = Invoice::where('company_id', $rawDisposal->id)->get();
            $allCustomers = Customer::where('company_id', $rawDisposal->id)->get();
        
        // Create payments for 80% of invoices (paid invoices)
        $paidInvoices = $allInvoices->take((int)($allInvoices->count() * 0.8));
        
        foreach ($paidInvoices as $invoice) {
            // Most invoices have full payment
            if (fake()->boolean(85)) {
                // Full payment
                $payment = Payment::factory()
                    ->create([
                        'company_id' => $rawDisposal->id,
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->customer_id,
                        'amount' => $invoice->total_amount,
                    ]);
                
                // Randomly assign payment method
                if (fake()->boolean(40)) {
                    // Credit card payment
                    Payment::factory()
                        ->creditCard()
                        ->create([
                            'company_id' => $rawDisposal->id,
                            'invoice_id' => $invoice->id,
                            'customer_id' => $invoice->customer_id,
                            'amount' => $invoice->total_amount,
                        ]);
                } elseif (fake()->boolean(30)) {
                    // Check payment
                    Payment::factory()
                        ->check()
                        ->create([
                            'company_id' => $rawDisposal->id,
                            'invoice_id' => $invoice->id,
                            'customer_id' => $invoice->customer_id,
                            'amount' => $invoice->total_amount,
                        ]);
                } elseif (fake()->boolean(20)) {
                    // Bank transfer
                    Payment::factory()
                        ->bankTransfer()
                        ->create([
                            'company_id' => $rawDisposal->id,
                            'invoice_id' => $invoice->id,
                            'customer_id' => $invoice->customer_id,
                            'amount' => $invoice->total_amount,
                        ]);
                } else {
                    // Cash payment
                    Payment::factory()
                        ->cash()
                        ->create([
                            'company_id' => $rawDisposal->id,
                            'invoice_id' => $invoice->id,
                            'customer_id' => $invoice->customer_id,
                            'amount' => $invoice->total_amount,
                        ]);
                }
                
                // Update invoice status to paid
                $invoice->update([
                    'status' => 'paid',
                    'paid_date' => now(),
                    'amount_paid' => $invoice->total_amount,
                    'balance_due' => 0,
                ]);
            } else {
                // Partial payment (50%)
                Payment::factory()
                    ->partial(50)
                    ->create([
                        'company_id' => $rawDisposal->id,
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->customer_id,
                    ]);
                
                // Update invoice with partial payment
                $invoice->update([
                    'status' => 'partial',
                    'amount_paid' => $invoice->total_amount * 0.5,
                    'balance_due' => $invoice->total_amount * 0.5,
                ]);
            }
        }
        
        // Create some payments made today
        foreach ($allCustomers->random(min(5, $allCustomers->count())) as $customer) {
            // Try to find an existing unpaid invoice for this customer
            $unpaidInvoice = Invoice::where('company_id', $rawDisposal->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'sent'])
                ->first();
            
            if (!$unpaidInvoice) {
                // Create a new invoice if no unpaid one exists
                $unpaidInvoice = Invoice::factory()->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)
                        ->where('customer_id', $customer->id)
                        ->first()?->id,
                ]);
            }
            
            Payment::factory()
                ->today()
                ->creditCard()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $unpaidInvoice->id,
                    'customer_id' => $customer->id,
                ]);
        }
        
        // Create some payments made this week
        foreach ($allCustomers->random(min(8, $allCustomers->count())) as $customer) {
            $unpaidInvoice = Invoice::where('company_id', $rawDisposal->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'sent'])
                ->first() ?? Invoice::factory()->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)
                        ->where('customer_id', $customer->id)
                        ->first()?->id,
                ]);
            
            Payment::factory()
                ->thisWeek()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $unpaidInvoice->id,
                    'customer_id' => $customer->id,
                ]);
        }
        
        // Create some payments made this month
        foreach ($allCustomers->random(min(10, $allCustomers->count())) as $customer) {
            $unpaidInvoice = Invoice::where('company_id', $rawDisposal->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'sent'])
                ->first() ?? Invoice::factory()->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)
                        ->where('customer_id', $customer->id)
                        ->first()?->id,
                ]);
            
            Payment::factory()
                ->thisMonth()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $unpaidInvoice->id,
                    'customer_id' => $customer->id,
                ]);
        }
        
        // Create some failed payments
        foreach ($allCustomers->random(min(3, $allCustomers->count())) as $customer) {
            $overdueInvoice = Invoice::where('company_id', $rawDisposal->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['overdue', 'pending'])
                ->first() ?? Invoice::factory()->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)
                        ->where('customer_id', $customer->id)
                        ->first()?->id,
                    'status' => 'overdue',
                ]);
            
            Payment::factory()
                ->failed()
                ->creditCard()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $overdueInvoice->id,
                    'customer_id' => $customer->id,
                ]);
        }
        
        // Create some pending payments
        foreach ($allCustomers->random(min(5, $allCustomers->count())) as $customer) {
            $unpaidInvoice = Invoice::where('company_id', $rawDisposal->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'sent'])
                ->first() ?? Invoice::factory()->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)
                        ->where('customer_id', $customer->id)
                        ->first()?->id,
                ]);
            
            Payment::factory()
                ->pending()
                ->bankTransfer()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $unpaidInvoice->id,
                    'customer_id' => $customer->id,
                ]);
        }
        
        // Create a few large amount payments
        foreach ($allCustomers->random(min(3, $allCustomers->count())) as $customer) {
            $largeInvoice = Invoice::factory()->create([
                'company_id' => $rawDisposal->id,
                'customer_id' => $customer->id,
                'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)
                    ->where('customer_id', $customer->id)
                    ->first()?->id,
                'total_amount' => fake()->randomFloat(2, 10000, 50000),
            ]);
            
            Payment::factory()
                ->largeAmount()
                ->creditCard()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $largeInvoice->id,
                    'customer_id' => $customer->id,
                ]);
        }
        
        // Create some PayPal payments
        foreach ($allCustomers->random(min(7, $allCustomers->count())) as $customer) {
            $unpaidInvoice = Invoice::where('company_id', $rawDisposal->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['pending', 'sent'])
                ->first() ?? Invoice::factory()->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)
                        ->where('customer_id', $customer->id)
                        ->first()?->id,
                ]);
            
            Payment::factory()
                ->paypal()
                ->create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $unpaidInvoice->id,
                    'customer_id' => $customer->id,
                ]);
        }
        
        // Create a refunded payment
        Payment::factory()
            ->refunded()
            ->creditCard()
            ->create([
                'company_id' => $rawDisposal->id,
                'invoice_id' => Invoice::factory()->create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $allCustomers->random()->id,
                    'service_order_id' => ServiceOrder::where('company_id', $rawDisposal->id)->first()?->id,
                    'status' => 'refunded',
                ])->id,
                'customer_id' => $allCustomers->random()->id,
            ]);
            
            $totalPayments = Payment::where('company_id', $rawDisposal->id)->count();
        }
        
        // Get pricing count
        $pricingCount = Pricing::where('company_id', $rawDisposal->id)->count();
        
        // Get work order count
        $workOrderCount = WorkOrder::where('company_id', $rawDisposal->id)->count();
        
        $this->command->info('RAW Disposal seeder completed successfully!');
        $this->command->info('Created:');
        $this->command->info(' - 1 RAW Disposal company');
        $this->command->info(' - 1 Admin user (admin@rawdisposal.com / password)');
        $this->command->info(' - 5 Driver users');
        $this->command->info(' - 5 Service areas');
        $this->command->info(' - 50 Customers (30 residential, 20 commercial)');
        $this->command->info(' - 75 Equipment units (dumpsters and portable toilets)');
        $this->command->info(" - {$pricingCount} Pricing plans (residential, commercial, construction, industrial)");
        $this->command->info(" - {$workOrderCount} Work orders (deliveries, pickups, swaps, various statuses)");
        $this->command->info(' - Multiple service orders, schedules, quotes, and invoices');
        $this->command->info(' - Maintenance logs and emergency service records');
        $this->command->info(' - 48 Delivery schedules (today, tomorrow, this week, completed, en route, cancelled)');
        
        $finalPaymentCount = Payment::where('company_id', $rawDisposal->id)->count();
        if ($finalPaymentCount > 0) {
            $this->command->info(" - {$finalPaymentCount} Payments (various methods: credit card, check, cash, bank transfer, PayPal)");
        }
    }
}