<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VehicleInspectionSeeder extends Seeder
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
        
        $this->command->info('Creating Vehicle Inspections...');
        
        $inspectionCount = 0;
        
        // Create inspections for each vehicle
        foreach ($vehicles as $vehicle) {
            // Create 2-4 inspections per vehicle
            $numInspections = $this->faker->numberBetween(2, 4);
            
            for ($i = 0; $i < $numInspections; $i++) {
                // Vary the inspection dates over the past year
                $inspectionDate = Carbon::now()->subDays($this->faker->numberBetween(0, 365));
                $hasIssues = $this->faker->boolean(30); // 30% chance of having issues
                
                VehicleInspection::create([
                    'company_id' => $company->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $drivers->random()->id,
                    'inspection_number' => VehicleInspection::generateInspectionNumber(),
                    'inspection_date' => $inspectionDate,
                    'inspection_time' => $inspectionDate->format('H:i:s'),
                    'inspection_type' => $this->faker->randomElement([
                        'daily', 'weekly', 'monthly', 'annual', 'dot', 'pre_trip', 'post_trip'
                    ]),
                    'status' => $this->determineInspectionStatus($inspectionDate, $hasIssues),
                    'odometer_reading' => $vehicle->mileage + $this->faker->numberBetween(0, 10000),
                    
                    // Inspection checklist items
                    'exterior_items' => $this->generateInspectionItems('exterior', $hasIssues),
                    'interior_items' => $this->generateInspectionItems('interior', $hasIssues),
                    'engine_items' => $this->generateInspectionItems('engine', $hasIssues),
                    'safety_items' => $this->generateInspectionItems('safety', $hasIssues),
                    'documentation_items' => $this->generateInspectionItems('documentation', false),
                    
                    // Issues and actions
                    'issues_found' => $hasIssues ? $this->generateIssues() : [],
                    'notes' => $hasIssues 
                        ? 'Issues found during inspection. ' . $this->faker->sentence() 
                        : 'Vehicle passed all inspection points.',
                    'corrective_actions' => $hasIssues 
                        ? 'Scheduled for maintenance. ' . $this->faker->sentence()
                        : null,
                    
                    // Inspector information
                    'inspector_name' => $this->faker->name(),
                    'inspector_signature' => strtoupper($this->faker->lexify('???')),
                    'inspector_certification_number' => 'CERT-' . $this->faker->numerify('####'),
                    'certified_at' => $inspectionDate->addHours(1),
                    
                    // Next inspection
                    'next_inspection_date' => $inspectionDate->copy()->addDays($this->faker->numberBetween(30, 90)),
                    'next_inspection_miles' => $vehicle->mileage + $this->faker->numberBetween(5000, 15000),
                ]);
                
                $inspectionCount++;
            }
        }
        
        $this->command->info("Created {$inspectionCount} vehicle inspections for LIV Transport.");
    }
    
    private function determineInspectionStatus($date, $hasIssues)
    {
        if ($date->isFuture()) {
            return 'scheduled';
        }
        
        if ($hasIssues) {
            return $this->faker->randomElement(['failed', 'needs_repair']);
        }
        
        return 'completed';
    }
    
    private function generateInspectionItems($category, $hasIssues = false)
    {
        $items = [];
        
        $itemsByCategory = [
            'exterior' => [
                'Tires - Front', 'Tires - Rear', 'Lights - Headlights', 'Lights - Taillights',
                'Lights - Turn Signals', 'Mirrors', 'Body Condition', 'Windshield', 'Wipers'
            ],
            'interior' => [
                'Seats', 'Seatbelts', 'Dashboard', 'Gauges', 'Horn', 'Controls',
                'Emergency Equipment', 'Fire Extinguisher', 'First Aid Kit'
            ],
            'engine' => [
                'Oil Level', 'Coolant Level', 'Brake Fluid', 'Power Steering Fluid',
                'Belts', 'Hoses', 'Battery', 'Air Filter', 'Exhaust System'
            ],
            'safety' => [
                'Brakes - Service', 'Brakes - Emergency', 'Steering', 'Suspension',
                'Warning Triangles', 'Reflectors', 'Emergency Exits'
            ],
            'documentation' => [
                'Registration', 'Insurance', 'DOT Permits', 'Driver License',
                'Medical Card', 'Log Book', 'Vehicle Inspection Report'
            ]
        ];
        
        $categoryItems = $itemsByCategory[$category] ?? ['Item 1', 'Item 2', 'Item 3'];
        $numItems = $this->faker->numberBetween(5, count($categoryItems));
        $selectedItems = $this->faker->randomElements($categoryItems, $numItems);
        
        foreach ($selectedItems as $itemName) {
            // Determine if this item has an issue
            $itemHasIssue = $hasIssues && $this->faker->boolean(20); // 20% chance if vehicle has issues
            
            $items[] = [
                'item' => $itemName,
                'status' => $itemHasIssue 
                    ? $this->faker->randomElement(['fail', 'needs_attention'])
                    : 'pass',
                'notes' => $itemHasIssue 
                    ? $this->faker->randomElement([
                        'Needs replacement', 'Worn', 'Below minimum', 'Requires attention',
                        'Not functioning properly', 'Damaged'
                    ])
                    : null,
            ];
        }
        
        return $items;
    }
    
    private function generateIssues()
    {
        $issues = [];
        $numIssues = $this->faker->numberBetween(1, 4);
        
        $possibleIssues = [
            ['issue' => 'Tire tread depth below minimum', 'severity' => 'major'],
            ['issue' => 'Oil leak detected', 'severity' => 'moderate'],
            ['issue' => 'Brake pads worn', 'severity' => 'major'],
            ['issue' => 'Coolant level low', 'severity' => 'minor'],
            ['issue' => 'Battery terminals corroded', 'severity' => 'minor'],
            ['issue' => 'Windshield crack', 'severity' => 'moderate'],
            ['issue' => 'Turn signal not working', 'severity' => 'major'],
            ['issue' => 'Air filter dirty', 'severity' => 'minor'],
            ['issue' => 'Exhaust system damage', 'severity' => 'critical'],
            ['issue' => 'Suspension wear detected', 'severity' => 'moderate'],
        ];
        
        $selectedIssues = $this->faker->randomElements($possibleIssues, $numIssues);
        
        foreach ($selectedIssues as $issue) {
            $issues[] = [
                'issue' => $issue['issue'],
                'severity' => $issue['severity'],
                'action_required' => $this->faker->randomElement([
                    'Replace immediately',
                    'Schedule maintenance within 7 days',
                    'Monitor and repair at next service',
                    'Repair before next trip',
                    'Replace at next maintenance interval'
                ]),
            ];
        }
        
        return $issues;
    }
}