<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\DeliverySchedule;
use App\Models\DisposalSite;
use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\EmergencyService;
use App\Models\Equipment;
use App\Models\FuelLog;
use App\Models\Invoice;
use App\Models\MaintenanceLog;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\Vehicle;
use App\Models\WasteCollection;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompleteDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Complete Dashboard Data Seeding...');
        
        // Get or create company
        $company = Company::first() ?? Company::create([
            'name' => 'Raw Disposal Services',
            'address' => '123 Waste Management Way',
            'city' => 'Houston',
            'state' => 'TX',
            'zip' => '77001',
            'phone' => '(713) 555-0100',
            'email' => 'info@rawdisposal.com',
            'website' => 'https://rawdisposal.com',
            'tax_id' => '12-3456789',
            'is_active' => true,
        ]);

        // Get existing counts
        $existingCustomers = Customer::where('company_id', $company->id)->get();
        $existingDrivers = Driver::where('company_id', $company->id)->get();
        
        // If we don't have enough customers, add more
        if ($existingCustomers->count() < 50) {
            $this->command->info('Adding more customers...');
            for ($i = $existingCustomers->count() + 1; $i <= 50; $i++) {
                Customer::create([
                    'company_id' => $company->id,
                    'customer_number' => 'CUST-DASH-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'organization' => fake()->company(),
                    'phone' => fake()->phoneNumber(),
                    'address' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->stateAbbr(),
                    'zip' => fake()->postcode(),
                    'customer_since' => Carbon::now()->subDays(rand(30, 730)),
                    'business_type' => fake()->randomElement(['Restaurant', 'Office', 'Retail', 'Industrial']),
                ]);
            }
            $existingCustomers = Customer::where('company_id', $company->id)->get();
        }

        // If we don't have enough drivers, add more
        if ($existingDrivers->count() < 15) {
            $this->command->info('Adding more drivers...');
            for ($i = $existingDrivers->count() + 1; $i <= 15; $i++) {
                Driver::create([
                    'company_id' => $company->id,
                    'employee_id' => 'DRV-DASH-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'phone' => fake()->phoneNumber(),
                    'email' => fake()->safeEmail(),
                    'license_number' => strtoupper(Str::random(8)),
                    'license_class' => 'CDL-A',
                    'license_expiry_date' => Carbon::now()->addMonths(rand(6, 36)),
                    'status' => 'active',
                    'hourly_rate' => fake()->randomFloat(2, 18, 35),
                    'hired_date' => Carbon::now()->subDays(rand(30, 1095)),
                ]);
            }
            $existingDrivers = Driver::where('company_id', $company->id)->get();
        }

        // 1. 🏭 Disposal Sites Status
        $this->command->info('1. Creating Disposal Sites...');
        $disposalSiteCount = DisposalSite::where('company_id', $company->id)->count();
        if ($disposalSiteCount < 5) {
            $siteTypes = ['landfill', 'recycling', 'composting', 'transfer_station', 'hazardous'];
            $siteNames = ['Central Landfill', 'North Recycling Center', 'Green Composting Facility', 'West Transfer Station', 'Hazmat Processing'];
            
            foreach ($siteTypes as $index => $type) {
                if (!DisposalSite::where('company_id', $company->id)->where('site_type', $type)->exists()) {
                    DisposalSite::create([
                        'company_id' => $company->id,
                        'name' => $siteNames[$index],
                        'location' => fake()->streetAddress() . ', Houston, TX',
                        'parish' => 'Harris County',
                        'site_type' => $type,
                        'total_capacity' => rand(100000, 500000),
                        'current_capacity' => rand(20000, 90000),
                        'daily_intake_average' => rand(100, 500),
                        'status' => fake()->randomElement(['active', 'active', 'active', 'maintenance']),
                        'manager_name' => fake()->name(),
                        'contact_phone' => fake()->phoneNumber(),
                        'operating_hours' => '6:00 AM - 6:00 PM',
                        'environmental_permit' => 'EPA-' . strtoupper(Str::random(8)),
                        'last_inspection_date' => Carbon::now()->subDays(rand(30, 180)),
                    ]);
                }
            }
        }

        // 2. ⏳ Pending Invoices (Already handled by existing invoices)
        $this->command->info('2. Creating Pending Invoices...');
        $pendingCount = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'sent'])
            ->count();
        
        if ($pendingCount < 20) {
            foreach ($existingCustomers->random(20 - $pendingCount) as $customer) {
                $serviceOrder = ServiceOrder::where('customer_id', $customer->id)->first();
                if (!$serviceOrder) {
                    $serviceOrder = ServiceOrder::create([
                        'company_id' => $company->id,
                        'customer_id' => $customer->id,
                        'order_number' => 'SO-DASH-' . str_pad(ServiceOrder::count() + 1, 6, '0', STR_PAD_LEFT),
                        'service_type' => 'delivery_pickup',
                        'delivery_date' => Carbon::now()->subDays(rand(1, 15)),
                        'status' => 'completed',
                    ]);
                }
                
                Invoice::create([
                    'company_id' => $company->id,
                    'service_order_id' => $serviceOrder->id,
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-PEND-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => Carbon::now()->subDays(rand(1, 10)),
                    'due_date' => Carbon::now()->addDays(rand(5, 30)),
                    'subtotal' => fake()->randomFloat(2, 200, 2000),
                    'tax_rate' => 8.25,
                    'tax_amount' => fake()->randomFloat(2, 16, 165),
                    'total_amount' => fake()->randomFloat(2, 216, 2165),
                    'balance_due' => fake()->randomFloat(2, 216, 2165),
                    'status' => fake()->randomElement(['draft', 'sent']),
                    'sent_date' => Carbon::now()->subDays(rand(1, 5)),
                ]);
            }
        }

        // 3. 📅 Today's Collection Schedule
        $this->command->info('3. Creating Today\'s Collection Schedules...');
        $todaySchedules = DeliverySchedule::where('company_id', $company->id)
            ->whereDate('scheduled_datetime', Carbon::today())
            ->count();
            
        if ($todaySchedules < 10) {
            $deliveryTypes = ['delivery', 'pickup', 'both'];
            $statuses = ['scheduled', 'in_progress', 'completed', 'cancelled'];
            
            for ($i = 0; $i < (10 - $todaySchedules); $i++) {
                $hour = rand(6, 16);
                DeliverySchedule::create([
                    'company_id' => $company->id,
                    'driver_id' => $existingDrivers->random()->id,
                    'type' => fake()->randomElement($deliveryTypes),
                    'scheduled_datetime' => Carbon::today()->setTime($hour, rand(0, 59)),
                    'actual_datetime' => fake()->boolean(60) ? Carbon::today()->setTime($hour + rand(0, 2), rand(0, 59)) : null,
                    'status' => fake()->randomElement($statuses),
                    'delivery_address' => fake()->streetAddress(),
                    'delivery_city' => fake()->city(),
                    'delivery_parish' => 'Harris County',
                    'delivery_postal_code' => fake()->postcode(),
                    'delivery_instructions' => fake()->optional()->sentence(),
                    'estimated_duration_minutes' => rand(15, 45),
                ]);
            }
        }

        // 4. 🚨 Emergency Services - Active Requests
        $this->command->info('4. Creating Emergency Services...');
        $activeEmergencies = EmergencyService::where('company_id', $company->id)
            ->whereIn('status', ['pending', 'assigned', 'dispatched', 'on_site'])
            ->count();
            
        if ($activeEmergencies < 3) {
            $emergencyTypes = ['delivery', 'pickup', 'cleaning', 'repair', 'replacement'];
            $priorities = ['low', 'medium', 'high', 'critical'];
            
            for ($i = 0; $i < (3 - $activeEmergencies); $i++) {
                $customer = $existingCustomers->random();
                EmergencyService::create([
                    'company_id' => $company->id,
                    'customer_id' => $customer->id,
                    'emergency_number' => 'EMG-' . str_pad(EmergencyService::count() + 1, 6, '0', STR_PAD_LEFT),
                    'emergency_type' => fake()->randomElement($emergencyTypes),
                    'location_address' => fake()->streetAddress(),
                    'location_city' => 'Houston',
                    'location_parish' => 'Harris County',
                    'location_postal_code' => fake()->postcode(),
                    'request_datetime' => Carbon::now()->subHours(rand(1, 12)),
                    'urgency_level' => fake()->randomElement($priorities),
                    'status' => fake()->randomElement(['pending', 'dispatched', 'on_site']),
                    'description' => fake()->paragraph(),
                    'assigned_driver_id' => fake()->optional()->randomElement($existingDrivers)?->id,
                    'contact_name' => fake()->name(),
                    'contact_phone' => fake()->phoneNumber(),
                    'total_cost' => fake()->randomFloat(2, 500, 10000),
                    'emergency_surcharge' => fake()->randomFloat(2, 100, 500),
                    'target_response_minutes' => fake()->randomElement([30, 60, 120]),
                    'equipment_needed' => fake()->randomElement(['Vacuum Truck', 'Crane', 'Hazmat Gear', 'Extra Bins']),
                ]);
            }
        }

        // 5. 🚨 Overdue Invoices - Action Required
        $this->command->info('5. Creating Overdue Invoices...');
        $overdueCount = Invoice::where('company_id', $company->id)
            ->where('status', 'overdue')
            ->count();
            
        if ($overdueCount < 10) {
            foreach ($existingCustomers->random(10 - $overdueCount) as $customer) {
                $serviceOrder = ServiceOrder::where('customer_id', $customer->id)->first();
                if (!$serviceOrder) {
                    $serviceOrder = ServiceOrder::create([
                        'company_id' => $company->id,
                        'customer_id' => $customer->id,
                        'order_number' => 'SO-OVR-' . str_pad(ServiceOrder::count() + 1, 6, '0', STR_PAD_LEFT),
                        'service_type' => 'delivery_pickup',
                        'delivery_date' => Carbon::now()->subDays(rand(40, 90)),
                        'status' => 'completed',
                    ]);
                }
                
                Invoice::create([
                    'company_id' => $company->id,
                    'service_order_id' => $serviceOrder->id,
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-OVR-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => Carbon::now()->subDays(rand(45, 90)),
                    'due_date' => Carbon::now()->subDays(rand(15, 44)),
                    'subtotal' => fake()->randomFloat(2, 300, 3000),
                    'tax_rate' => 8.25,
                    'tax_amount' => fake()->randomFloat(2, 25, 248),
                    'total_amount' => fake()->randomFloat(2, 325, 3248),
                    'balance_due' => fake()->randomFloat(2, 325, 3248),
                    'status' => 'overdue',
                ]);
            }
        }

        // 6. Assignment Activity - Last 7 Days
        $this->command->info('6. Creating Driver Assignments (Last 7 Days)...');
        $recentAssignments = DriverAssignment::where('company_id', $company->id)
            ->where('start_date', '>=', Carbon::now()->subDays(7))
            ->count();
            
        if ($recentAssignments < 50) {
            $statuses = ['scheduled', 'active', 'completed', 'cancelled'];
            $cargoTypes = ['General Waste', 'Recyclables', 'Organic Waste', 'Construction Debris'];
            
            for ($i = 0; $i < (50 - $recentAssignments); $i++) {
                $startDate = Carbon::now()->subDays(rand(0, 7))->setTime(rand(6, 18), rand(0, 59));
                $status = fake()->randomElement($statuses);
                
                $duration = rand(4, 10);
                DriverAssignment::create([
                    'company_id' => $company->id,
                    'driver_id' => $existingDrivers->random()->id,
                    'vehicle_id' => null, // Will be handled by Vehicle if exists
                    'route' => fake()->randomElement(['Route A', 'Route B', 'Route C', 'Route D']),
                    'start_date' => $startDate,
                    'end_date' => $status === 'completed' ? $startDate->copy()->addHours($duration) : null,
                    'status' => $status,
                    'cargo_type' => fake()->randomElement($cargoTypes),
                    'cargo_weight' => $status === 'completed' ? rand(1000, 8000) : 0,
                    'expected_duration_hours' => $duration,
                    'actual_duration_hours' => $status === 'completed' ? $duration : null,
                    'origin' => fake()->address(),
                    'destination' => 'Central Disposal Site',
                ]);
            }
        }

        // 7. 📊 Waste Collection Trends - Last 30 Days
        $this->command->info('7. Creating Waste Collection Data (Last 30 Days)...');
        $wasteCollections = WasteCollection::where('company_id', $company->id)
            ->where('scheduled_date', '>=', Carbon::now()->subDays(30))
            ->count();
            
        if ($wasteCollections < 100) {
            $wasteTypes = ['general', 'recyclable', 'organic', 'hazardous'];
            
            for ($day = 29; $day >= 0; $day--) {
                $date = Carbon::now()->subDays($day);
                // Create 3-5 collections per day
                for ($i = 0; $i < rand(3, 5); $i++) {
                    WasteCollection::create([
                        'company_id' => $company->id,
                        'customer_id' => $existingCustomers->random()->id,
                        'driver_id' => $existingDrivers->random()->id,
                        'truck_id' => null, // Will be handled by Vehicle if exists
                        'scheduled_date' => $date,
                        'scheduled_time' => fake()->time(),
                        'waste_type' => fake()->randomElement($wasteTypes),
                        'estimated_weight' => fake()->randomFloat(2, 100, 5000), // kg
                        'actual_weight' => fake()->optional()->randomFloat(2, 100, 5000),
                        'status' => fake()->randomElement(['scheduled', 'completed', 'missed', 'rescheduled']),
                        'completed_at' => fake()->boolean(70) ? $date->copy()->addHours(rand(1, 8)) : null,
                        'route_id' => null,
                        'notes' => fake()->optional()->sentence(),
                    ]);
                }
            }
        }

        // 8. Fuel Consumption & Costs - Last 30 Days
        $this->command->info('8. Creating Fuel Logs (Last 30 Days)...');
        
        // Create some vehicles if we don't have any
        $vehicles = Vehicle::where('company_id', $company->id)->get();
        if ($vehicles->count() < 10) {
            $existingCount = $vehicles->count();
            for ($i = $existingCount + 1; $i <= 10; $i++) {
                $unitNumber = 'VEH-' . str_pad($i, 4, '0', STR_PAD_LEFT);
                
                // Skip if already exists
                if (Vehicle::where('unit_number', $unitNumber)->exists()) {
                    continue;
                }
                
                Vehicle::create([
                    'company_id' => $company->id,
                    'unit_number' => $unitNumber,
                    'make' => fake()->randomElement(['Mack', 'Peterbilt', 'Volvo', 'Freightliner']),
                    'model' => fake()->randomElement(['LR', 'CNG', 'ACX', 'Cascadia']),
                    'year' => rand(2018, 2024),
                    'license_plate' => strtoupper(Str::random(3)) . '-' . rand(1000, 9999),
                    'vin' => strtoupper(Str::random(17)),
                    'status' => 'active',
                    'type' => 'truck',
                    'fuel_type' => fake()->randomElement(['diesel', 'gasoline', 'hybrid']),
                    'odometer' => rand(10000, 150000),
                    'odometer_date' => Carbon::now(),
                    'is_leased' => fake()->boolean(30),
                ]);
            }
            $vehicles = Vehicle::where('company_id', $company->id)->get();
        }
        
        $fuelLogs = FuelLog::where('company_id', $company->id)
            ->where('fuel_date', '>=', Carbon::now()->subDays(30))
            ->count();
            
        if ($fuelLogs < 60 && $vehicles->count() > 0) {
            for ($day = 29; $day >= 0; $day -= rand(2, 5)) {
                foreach ($vehicles->random(min(5, $vehicles->count())) as $vehicle) {
                    $gallons = fake()->randomFloat(2, 20, 80);
                    $pricePerGallon = fake()->randomFloat(2, 3.50, 5.00);
                    FuelLog::create([
                        'company_id' => $company->id,
                        'vehicle_id' => $vehicle->id,
                        'driver_id' => $existingDrivers->random()->id,
                        'fuel_date' => Carbon::now()->subDays($day),
                        'fuel_type' => in_array($vehicle->fuel_type, ['diesel']) ? 'diesel' : 'regular',
                        'gallons' => $gallons,
                        'price_per_gallon' => $pricePerGallon,
                        'total_cost' => round($gallons * $pricePerGallon, 2),
                        'odometer_reading' => $vehicle->odometer + rand(100, 1000),
                        'fuel_station' => fake()->company() . ' Gas Station',
                        'location' => fake()->city() . ', TX',
                        'payment_method' => fake()->randomElement(['company_card', 'cash', 'credit_card']),
                    ]);
                }
            }
        }

        // 9. 🏆 Top Driver Performance - This Month
        $this->command->info('9. Generating Driver Performance Data...');
        // Driver performance is calculated from existing assignments and safety scores
        // Update driver safety scores to show variation
        foreach ($existingDrivers as $driver) {
            $driver->update([
                'safety_score' => rand(75, 100),
                'total_miles_driven' => rand(5000, 50000),
            ]);
        }

        // 10. Recent Driver Assignments
        $this->command->info('10. Creating Recent Driver Assignments...');
        $recentCount = DriverAssignment::where('company_id', $company->id)
            ->where('start_date', '>=', Carbon::now()->subDays(3))
            ->count();
            
        if ($recentCount < 20) {
            for ($i = 0; $i < (20 - $recentCount); $i++) {
                $startDate = Carbon::now()->subDays(rand(0, 3))->setTime(rand(6, 18), rand(0, 59));
                
                $duration = rand(6, 10);
                DriverAssignment::create([
                    'company_id' => $company->id,
                    'driver_id' => $existingDrivers->random()->id,
                    'vehicle_id' => $vehicles->isNotEmpty() ? $vehicles->random()->id : null,
                    'route' => 'Recent Route ' . ($i + 1),
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addHours($duration),
                    'status' => 'completed',
                    'cargo_type' => 'Mixed Waste',
                    'cargo_weight' => rand(2000, 6000),
                    'expected_duration_hours' => $duration,
                    'actual_duration_hours' => $duration,
                    'mileage_start' => rand(50000, 100000),
                    'mileage_end' => rand(100001, 150000),
                    'origin' => fake()->address(),
                    'destination' => 'Processing Center',
                ]);
            }
        }

        // 11. Fleet Utilization
        $this->command->info('11. Creating Fleet/Equipment Data...');
        $equipment = Equipment::where('company_id', $company->id)->get();
        
        if ($equipment->count() < 15) {
            $equipmentTypes = ['Rear Loader', 'Side Loader', 'Front Loader', 'Roll-off Truck', 'Grapple Truck'];
            $statuses = ['active', 'active', 'active', 'maintenance', 'repair'];
            
            for ($i = $equipment->count() + 1; $i <= 15; $i++) {
                Equipment::create([
                    'company_id' => $company->id,
                    'equipment_number' => 'EQP-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'type' => fake()->randomElement($equipmentTypes),
                    'make' => fake()->randomElement(['Mack', 'Peterbilt', 'Volvo']),
                    'model' => 'Model ' . rand(2020, 2024),
                    'year' => rand(2018, 2024),
                    'status' => fake()->randomElement($statuses),
                    'purchase_date' => Carbon::now()->subDays(rand(180, 1095)),
                    'purchase_price' => fake()->randomFloat(2, 80000, 250000),
                    'current_mileage' => rand(10000, 150000),
                    'capacity' => rand(10, 30) . ' cubic yards',
                    'license_plate' => strtoupper(Str::random(3)) . '-' . rand(1000, 9999),
                ]);
            }
        }

        // Update equipment utilization - link equipment as vehicles in assignments
        // Since equipment_id doesn't exist in driver_assignments, we'll track utilization differently

        // Summary
        $this->command->info('');
        $this->command->info('✅ Dashboard Data Seeding Complete!');
        $this->command->info('=====================================');
        
        $this->command->table(
            ['Widget', 'Data Count'],
            [
                ['🏭 Disposal Sites', DisposalSite::where('company_id', $company->id)->count()],
                ['⏳ Pending Invoices', Invoice::where('company_id', $company->id)->whereIn('status', ['pending', 'sent'])->count()],
                ['📅 Today\'s Schedules', DeliverySchedule::where('company_id', $company->id)->whereDate('scheduled_datetime', Carbon::today())->count()],
                ['🚨 Active Emergencies', EmergencyService::where('company_id', $company->id)->whereIn('status', ['pending', 'assigned', 'dispatched', 'on_site'])->count()],
                ['🚨 Overdue Invoices', Invoice::where('company_id', $company->id)->where('status', 'overdue')->count()],
                ['📊 Assignments (7 days)', DriverAssignment::where('company_id', $company->id)->where('start_date', '>=', Carbon::now()->subDays(7))->count()],
                ['📊 Waste Collections (30 days)', WasteCollection::where('company_id', $company->id)->where('scheduled_date', '>=', Carbon::now()->subDays(30))->count()],
                ['⛽ Fuel Logs (30 days)', FuelLog::where('company_id', $company->id)->where('fuel_date', '>=', Carbon::now()->subDays(30))->count()],
                ['🚛 Vehicles', Vehicle::where('company_id', $company->id)->count()],
                ['🔧 Equipment', Equipment::where('company_id', $company->id)->count()],
                ['👷 Active Drivers', Driver::where('company_id', $company->id)->where('status', 'active')->count()],
            ]
        );
        
        $this->command->info('');
        $this->command->info('All dashboard widgets should now have data!');
    }
}