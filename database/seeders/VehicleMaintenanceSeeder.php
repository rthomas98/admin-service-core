<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleMaintenance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VehicleMaintenanceSeeder extends Seeder
{
    protected $faker;
    
    public function __construct()
    {
        $this->faker = Faker::create();
    }
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find LIV Transport company
        $company = Company::where('name', 'LIKE', '%LIV Transport%')->first();
        
        if (!$company) {
            $this->command->error('LIV Transport company not found. Please run LivTransportSeeder first.');
            return;
        }
        
        // Get vehicles for this company
        $vehicles = Vehicle::where('company_id', $company->id)->get();
        
        if ($vehicles->isEmpty()) {
            $this->command->error('No vehicles found for LIV Transport. Please run LivTransportSeeder first.');
            return;
        }
        
        // Get drivers
        $drivers = Driver::where('company_id', $company->id)->get();
        
        if ($drivers->isEmpty()) {
            $this->command->error('No drivers found for LIV Transport. Please run LivTransportSeeder first.');
            return;
        }
        
        $this->command->info('Creating Vehicle Maintenance Records...');
        
        $maintenanceCount = 0;
        
        // Create maintenance records for each vehicle
        foreach ($vehicles as $vehicle) {
            // Create 3-6 maintenance records per vehicle
            $numMaintenances = $this->faker->numberBetween(3, 6);
            
            for ($i = 0; $i < $numMaintenances; $i++) {
                // Vary the maintenance dates over the past year
                $scheduledDate = Carbon::now()->subDays($this->faker->numberBetween(0, 365));
                $isCompleted = $scheduledDate->isPast() && $this->faker->boolean(80); // 80% completed
                
                VehicleMaintenance::create([
                    'company_id' => $company->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $drivers->random()->id,
                    'maintenance_number' => VehicleMaintenance::generateMaintenanceNumber(),
                    'maintenance_type' => $this->faker->randomElement([
                        'preventive', 'corrective', 'emergency', 'scheduled', 'oil_change', 
                        'tire_rotation', 'brake_service', 'engine_service', 'transmission_service',
                        'cooling_system', 'electrical', 'body_work', 'other'
                    ]),
                    'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
                    'status' => $this->determineMaintenanceStatus($scheduledDate, $isCompleted),
                    'scheduled_date' => $scheduledDate,
                    'scheduled_time' => $scheduledDate->format('H:i:s'),
                    'completed_date' => $isCompleted ? $scheduledDate->addDays($this->faker->numberBetween(0, 3)) : null,
                    'completed_time' => $isCompleted ? $this->faker->time() : null,
                    'description' => $this->generateMaintenanceDescription(),
                    
                    // Work details
                    'work_performed' => $this->generateWorkPerformed($isCompleted),
                    'parts_replaced' => $this->generatePartsReplaced(),
                    'fluids_added' => $this->generateFluidsAdded(),
                    
                    // Vehicle data
                    'odometer_at_service' => $vehicle->mileage + $this->faker->numberBetween(0, 15000),
                    'next_service_miles' => $vehicle->mileage + $this->faker->numberBetween(3000, 20000),
                    'next_service_date' => $scheduledDate->copy()->addDays($this->faker->numberBetween(30, 180)),
                    
                    // Service provider
                    'service_provider' => $this->faker->randomElement([
                        'In-House Maintenance', 'LIV Service Center', 
                        $this->faker->company . ' Auto Service',
                        $this->faker->company . ' Fleet Services'
                    ]),
                    'technician_name' => $this->faker->name(),
                    'work_order_number' => 'WO-' . $this->faker->numerify('######'),
                    'invoice_number' => $isCompleted ? 'INV-' . $this->faker->numerify('######') : null,
                    
                    // Costs
                    'labor_cost' => $this->faker->randomFloat(2, 50, 800),
                    'parts_cost' => $this->faker->randomFloat(2, 0, 1500),
                    'other_cost' => $this->faker->randomFloat(2, 0, 200),
                    'total_cost' => 0, // Will be calculated
                    'payment_status' => $isCompleted ? $this->faker->randomElement(['pending', 'paid', 'partial', 'disputed']) : 'pending',
                    'under_warranty' => $this->faker->boolean(20), // 20% under warranty
                    'warranty_claim_number' => $this->faker->boolean(20) ? 'WC-' . $this->faker->numerify('######') : null,
                    'warranty_covered_amount' => $this->faker->boolean(20) ? $this->faker->randomFloat(2, 100, 1000) : 0,
                    
                    // Downtime
                    'vehicle_down_from' => $isCompleted ? $scheduledDate : null,
                    'vehicle_down_to' => $isCompleted ? $scheduledDate->copy()->addHours($this->faker->numberBetween(2, 48)) : null,
                    'total_downtime_hours' => $isCompleted ? $this->faker->numberBetween(2, 48) : null,
                    
                    // Additional info
                    'notes' => $this->faker->sentence(),
                    'recommendations' => $this->faker->boolean(60) ? $this->generateRecommendations() : null,
                ]);
                
                $maintenanceCount++;
            }
        }
        
        // Update total costs for all maintenance records
        VehicleMaintenance::where('company_id', $company->id)
            ->whereNull('total_cost')
            ->orWhere('total_cost', 0)
            ->each(function ($maintenance) {
                $maintenance->update([
                    'total_cost' => $maintenance->labor_cost + $maintenance->parts_cost + $maintenance->other_cost
                ]);
            });
        
        $this->command->info("Created {$maintenanceCount} vehicle maintenance records for LIV Transport.");
    }
    
    private function determineMaintenanceStatus($date, $isCompleted)
    {
        if ($date->isFuture()) {
            return 'scheduled';
        }
        
        if (!$isCompleted) {
            return $this->faker->randomElement(['in_progress', 'on_hold', 'scheduled']);
        }
        
        return 'completed';
    }
    
    private function generateMaintenanceDescription()
    {
        $descriptions = [
            'Regular preventive maintenance service',
            'Oil and filter change with multi-point inspection',
            'Brake system inspection and service',
            'Tire rotation and wheel alignment',
            'Engine diagnostic and tune-up',
            'Transmission service and fluid change',
            'Cooling system flush and refill',
            'Battery and electrical system check',
            'Suspension and steering inspection',
            'Air filter and cabin filter replacement',
            'DOT annual inspection and compliance',
            'Emergency brake repair',
            'Scheduled 30,000 mile service',
            'Scheduled 60,000 mile service',
            'Scheduled 90,000 mile service',
        ];
        
        return $this->faker->randomElement($descriptions) . '. ' . $this->faker->sentence();
    }
    
    private function generateWorkPerformed($isCompleted)
    {
        if (!$isCompleted) {
            return [];
        }
        
        $tasks = [];
        $numTasks = $this->faker->numberBetween(2, 5);
        
        $possibleTasks = [
            'Oil and filter change',
            'Brake pad replacement',
            'Tire rotation',
            'Battery test and service',
            'Coolant system check',
            'Transmission fluid change',
            'Air filter replacement',
            'Spark plug replacement',
            'Belt inspection',
            'Hose inspection',
            'Wheel alignment',
            'Exhaust system check',
            'Suspension check',
            'Electrical system diagnostic',
            'Fuel system cleaning',
        ];
        
        $selectedTasks = $this->faker->randomElements($possibleTasks, $numTasks);
        
        foreach ($selectedTasks as $task) {
            $tasks[] = [
                'task' => $task,
                'status' => $this->faker->randomElement(['completed', 'completed', 'partial']),
                'notes' => $this->faker->boolean(30) ? $this->faker->sentence(3) : null,
            ];
        }
        
        return $tasks;
    }
    
    private function generatePartsReplaced()
    {
        if ($this->faker->boolean(60)) { // 60% chance of parts replacement
            $parts = [];
            $numParts = $this->faker->numberBetween(1, 4);
            
            $possibleParts = [
                ['name' => 'Oil Filter', 'number' => 'OF-' . $this->faker->numerify('####'), 'cost' => 15.99],
                ['name' => 'Air Filter', 'number' => 'AF-' . $this->faker->numerify('####'), 'cost' => 25.99],
                ['name' => 'Brake Pads', 'number' => 'BP-' . $this->faker->numerify('####'), 'cost' => 89.99],
                ['name' => 'Battery', 'number' => 'BAT-' . $this->faker->numerify('####'), 'cost' => 149.99],
                ['name' => 'Spark Plugs', 'number' => 'SP-' . $this->faker->numerify('####'), 'cost' => 45.99],
                ['name' => 'Wiper Blades', 'number' => 'WB-' . $this->faker->numerify('####'), 'cost' => 29.99],
                ['name' => 'Cabin Filter', 'number' => 'CF-' . $this->faker->numerify('####'), 'cost' => 19.99],
                ['name' => 'Belt', 'number' => 'BLT-' . $this->faker->numerify('####'), 'cost' => 39.99],
                ['name' => 'Hose', 'number' => 'HSE-' . $this->faker->numerify('####'), 'cost' => 24.99],
                ['name' => 'Tire', 'number' => 'TR-' . $this->faker->numerify('####'), 'cost' => 189.99],
            ];
            
            $selectedParts = $this->faker->randomElements($possibleParts, $numParts);
            
            foreach ($selectedParts as $part) {
                $parts[] = [
                    'part_name' => $part['name'],
                    'part_number' => $part['number'],
                    'quantity' => $part['name'] === 'Tire' || $part['name'] === 'Spark Plugs' 
                        ? $this->faker->numberBetween(2, 4) : 1,
                    'cost' => $part['cost'],
                ];
            }
            
            return $parts;
        }
        
        return [];
    }
    
    private function generateFluidsAdded()
    {
        if ($this->faker->boolean(70)) { // 70% chance of fluids being added
            $fluids = [];
            $numFluids = $this->faker->numberBetween(1, 3);
            
            $possibleFluids = [
                ['type' => 'Engine Oil', 'quantity' => $this->faker->randomElement([5, 6, 7]), 'cost' => 29.99],
                ['type' => 'Coolant', 'quantity' => $this->faker->randomElement([1, 2]), 'cost' => 15.99],
                ['type' => 'Transmission Fluid', 'quantity' => $this->faker->randomElement([3, 4, 5]), 'cost' => 45.99],
                ['type' => 'Brake Fluid', 'quantity' => 1, 'cost' => 12.99],
                ['type' => 'Power Steering Fluid', 'quantity' => 1, 'cost' => 9.99],
                ['type' => 'Windshield Washer Fluid', 'quantity' => 1, 'cost' => 4.99],
                ['type' => 'Differential Fluid', 'quantity' => $this->faker->randomElement([1, 2]), 'cost' => 34.99],
            ];
            
            $selectedFluids = $this->faker->randomElements($possibleFluids, $numFluids);
            
            foreach ($selectedFluids as $fluid) {
                $fluids[] = [
                    'fluid_type' => $fluid['type'],
                    'quantity' => $fluid['quantity'],
                    'cost' => $fluid['cost'],
                ];
            }
            
            return $fluids;
        }
        
        return [];
    }
    
    private function generateRecommendations()
    {
        $recommendations = [
            'Recommend tire replacement within next 5,000 miles due to wear pattern.',
            'Battery showing signs of age, recommend replacement before winter.',
            'Brake pads at 40% - plan replacement at next service.',
            'Coolant system needs flush at next major service interval.',
            'Transmission service recommended at 60,000 miles.',
            'Front suspension bushings showing wear - monitor and replace if needed.',
            'Engine air filter will need replacement at next service.',
            'Recommend wheel alignment check due to uneven tire wear.',
            'Power steering fluid dark - recommend flush and replacement.',
            'Check engine light intermittent - recommend diagnostic at next visit.',
        ];
        
        return $this->faker->randomElement($recommendations);
    }
}