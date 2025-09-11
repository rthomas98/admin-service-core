<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use App\Models\VehicleMaintenance;
use App\Models\FuelLog;
use Carbon\Carbon;

class VehicleFeatureSeeder extends Seeder
{
    public function run(): void
    {
        // Get John Smith's driver record
        $user = User::where('email', 'john.smith@livtransport.com')->first();
        if (!$user) {
            $this->command->error('John Smith user not found. Run FieldAppDriverSeeder first.');
            return;
        }
        
        $driver = Driver::where('user_id', $user->id)->first();
        if (!$driver) {
            $this->command->error('John Smith driver record not found.');
            return;
        }
        
        $companyId = $driver->company_id;
        $vehicle = Vehicle::where('company_id', $companyId)->first();
        
        if (!$vehicle) {
            $this->command->error('No vehicle found for company.');
            return;
        }
        
        $this->command->info('Seeding vehicle features data...');
        
        // Seed Vehicle Inspections (30 days of history)
        $this->seedVehicleInspections($companyId, $driver->id, $vehicle->id);
        
        // Seed Vehicle Maintenance Records
        $this->seedVehicleMaintenance($companyId, $vehicle->id);
        
        // Seed Fuel Logs (30 days of history)
        $this->seedFuelLogs($companyId, $driver->id, $vehicle->id);
        
        $this->command->info('Vehicle features data seeded successfully!');
    }
    
    private function seedVehicleInspections($companyId, $driverId, $vehicleId): void
    {
        $this->command->info('Creating vehicle inspection records...');
        
        // Generate inspections for the last 30 days
        for ($days = 30; $days >= 0; $days--) {
            $date = Carbon::now()->subDays($days);
            
            // Skip weekends for most inspections
            if ($date->isWeekend() && rand(0, 100) > 20) {
                continue;
            }
            
            // Morning pre-trip inspection
            $inspectionNumber = 'INSP-' . $date->format('Ymd') . '-001';
            $passed = rand(0, 100) > 10; // 90% pass rate
            
            VehicleInspection::firstOrCreate(
                ['inspection_number' => $inspectionNumber],
                [
                    'company_id' => $companyId,
                    'driver_id' => $driverId,
                    'vehicle_id' => $vehicleId,
                    'inspection_type' => 'pre_trip',
                    'inspection_date' => $date->toDateString(),
                    'inspection_time' => '07:00:00',
                    'status' => $passed ? 'completed' : 'failed',
                    'odometer_reading' => 125000 + ($days * 250) + rand(0, 100),
                    'issues_found' => !$passed ? $this->getRandomIssue() : null,
                    'notes' => $this->getRandomInspectionNote(),
                    'inspector_name' => 'John Smith',
                    'exterior_items' => json_encode($this->getExteriorChecklist($passed)),
                    'interior_items' => json_encode($this->getInteriorChecklist()),
                    'engine_items' => json_encode($this->getEngineChecklist($passed)),
                    'safety_items' => json_encode($this->getSafetyChecklist()),
                ]
            );
            
            // Evening post-trip inspection (80% of days)
            if (rand(0, 100) > 20) {
                $inspectionNumber = 'INSP-' . $date->format('Ymd') . '-002';
                $passed = rand(0, 100) > 5; // 95% pass rate for post-trip
                
                VehicleInspection::firstOrCreate(
                    ['inspection_number' => $inspectionNumber],
                    [
                        'company_id' => $companyId,
                        'driver_id' => $driverId,
                        'vehicle_id' => $vehicleId,
                        'inspection_type' => 'post_trip',
                        'inspection_date' => $date->toDateString(),
                        'inspection_time' => '18:00:00',
                        'status' => $passed ? 'completed' : 'needs_repair',
                        'odometer_reading' => 125000 + ($days * 250) + 200 + rand(0, 100),
                        'issues_found' => !$passed ? $this->getRandomPostTripIssue() : null,
                        'notes' => 'End of shift inspection',
                        'inspector_name' => 'John Smith',
                        'exterior_items' => json_encode($this->getExteriorChecklist($passed)),
                        'interior_items' => json_encode($this->getInteriorChecklist()),
                        'engine_items' => json_encode($this->getEngineChecklist()),
                        'safety_items' => json_encode($this->getSafetyChecklist()),
                    ]
                );
            }
        }
        
        $this->command->info('Created vehicle inspection records.');
    }
    
    private function seedVehicleMaintenance($companyId, $vehicleId): void
    {
        $this->command->info('Creating vehicle maintenance records...');
        
        $maintenanceRecords = [
            // Completed maintenance
            [
                'service_type' => 'oil_change',
                'service_date' => Carbon::now()->subDays(15),
                'next_service_date' => Carbon::now()->addDays(75),
                'mileage_at_service' => 122500,
                'next_service_mileage' => 127500,
                'service_provider' => 'Quick Lube Express',
                'cost' => 89.99,
                'description' => 'Regular oil change and filter replacement',
                'parts_replaced' => 'Oil filter, 15W-40 engine oil (12 quarts)',
                'labor_hours' => 0.5,
                'status' => 'completed',
            ],
            [
                'service_type' => 'tire_rotation',
                'service_date' => Carbon::now()->subDays(30),
                'next_service_date' => Carbon::now()->addDays(60),
                'mileage_at_service' => 120000,
                'next_service_mileage' => 130000,
                'service_provider' => 'Fleet Tire Service',
                'cost' => 120.00,
                'description' => 'Rotate all tires and check tire pressure',
                'parts_replaced' => null,
                'labor_hours' => 1.0,
                'status' => 'completed',
            ],
            [
                'service_type' => 'brake_service',
                'service_date' => Carbon::now()->subDays(45),
                'next_service_date' => Carbon::now()->addDays(135),
                'mileage_at_service' => 118000,
                'next_service_mileage' => 138000,
                'service_provider' => 'Commercial Truck Center',
                'cost' => 150.00,
                'description' => 'Complete brake system inspection and adjustment',
                'parts_replaced' => 'Brake adjustment only',
                'labor_hours' => 2.0,
                'status' => 'completed',
            ],
            [
                'service_type' => 'preventive',
                'service_date' => Carbon::now()->subDays(60),
                'mileage_at_service' => 115000,
                'service_provider' => 'Fleet Maintenance Shop',
                'cost' => 75.00,
                'description' => 'Replace engine air filter',
                'parts_replaced' => 'Engine air filter',
                'labor_hours' => 0.5,
                'status' => 'completed',
            ],
            
            // Pending/Scheduled maintenance
            [
                'service_type' => 'transmission_service',
                'service_date' => Carbon::now()->addDays(10),
                'mileage_at_service' => 125000,
                'service_provider' => 'Commercial Truck Center',
                'cost' => 450.00,
                'description' => 'Transmission fluid change and filter replacement',
                'status' => 'scheduled',
            ],
            [
                'service_type' => 'cooling_system',
                'service_date' => Carbon::now()->addDays(30),
                'mileage_at_service' => 128000,
                'service_provider' => 'Fleet Maintenance Shop',
                'cost' => 180.00,
                'description' => 'Complete coolant system flush and refill',
                'status' => 'scheduled',
            ],
            
            // Recent issues reported
            [
                'service_type' => 'engine_service',
                'service_date' => Carbon::now()->subDays(2),
                'mileage_at_service' => 124800,
                'description' => 'Check engine light came on during route',
                'reported_by' => 'John Smith',
                'status' => 'scheduled',
                'severity' => 'medium',
                'notes' => 'Driver reported intermittent check engine light',
            ],
            [
                'service_type' => 'other',
                'service_date' => Carbon::now()->subDays(1),
                'mileage_at_service' => 124950,
                'description' => 'Air conditioning not cooling properly',
                'reported_by' => 'John Smith',
                'status' => 'scheduled',
                'severity' => 'low',
                'notes' => 'AC blowing warm air, needs refrigerant check',
            ],
            [
                'service_type' => 'corrective',
                'service_date' => Carbon::now(),
                'mileage_at_service' => 125100,
                'description' => 'Steering wheel vibrates at highway speeds',
                'reported_by' => 'John Smith',
                'status' => 'scheduled',
                'severity' => 'high',
                'notes' => 'Vibration starts at 55mph, needs immediate attention',
            ],
        ];
        
        foreach ($maintenanceRecords as $record) {
            $maintenanceNumber = 'MAINT-' . Carbon::parse($record['service_date'])->format('Ymd') . '-' . rand(100, 999);
            
            // Map old field names to correct database column names
            $mappedRecord = [
                'company_id' => $companyId,
                'vehicle_id' => $vehicleId,
                'maintenance_number' => $maintenanceNumber,
                'maintenance_type' => $record['service_type'],
                'scheduled_date' => isset($record['service_date']) ? Carbon::parse($record['service_date'])->toDateString() : null,
                'completed_date' => isset($record['status']) && $record['status'] === 'completed' ? Carbon::parse($record['service_date'])->toDateString() : null,
                'next_service_date' => $record['next_service_date'] ?? null,
                'odometer_at_service' => $record['mileage_at_service'] ?? null,
                'next_service_miles' => $record['next_service_mileage'] ?? null,
                'service_provider' => $record['service_provider'] ?? null,
                'total_cost' => $record['cost'] ?? 0,
                'description' => $record['description'] ?? null,
                'parts_replaced' => $record['parts_replaced'] ?? null,
                'labor_cost' => isset($record['labor_hours']) ? $record['labor_hours'] * 75 : 0, // Assuming $75/hour labor
                'status' => $record['status'] ?? 'scheduled',
                'priority' => $record['severity'] ?? 'medium',
                'notes' => $record['notes'] ?? null,
                'technician_name' => $record['reported_by'] ?? null,
            ];
            
            VehicleMaintenance::firstOrCreate(
                ['maintenance_number' => $maintenanceNumber],
                $mappedRecord
            );
        }
        
        $this->command->info('Created vehicle maintenance records.');
    }
    
    private function seedFuelLogs($companyId, $driverId, $vehicleId): void
    {
        $this->command->info('Creating fuel log records...');
        
        $fuelStations = [
            'Pilot Flying J - Chicago',
            'Love\'s Travel Stop - Aurora',
            'TA Travel Center - Joliet',
            'Speedway - Des Plaines',
            'Shell Truck Stop - Naperville',
            'BP Travel Plaza - Schaumburg',
            'Marathon - Oak Brook',
        ];
        
        $currentOdometer = 125000;
        
        // Generate fuel logs for the last 30 days
        for ($days = 30; $days >= 0; $days -= rand(2, 4)) {
            $date = Carbon::now()->subDays($days);
            $gallons = rand(40, 80) + (rand(0, 99) / 100);
            $pricePerGallon = rand(350, 450) / 100;
            $totalCost = round($gallons * $pricePerGallon, 2);
            $odometer = $currentOdometer - ($days * 250) + rand(0, 100);
            
            FuelLog::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'vehicle_id' => $vehicleId,
                    'driver_id' => $driverId,
                    'fuel_date' => $date->toDateString(),
                ],
                [
                    'fuel_station' => $fuelStations[array_rand($fuelStations)],
                    'location' => $fuelStations[array_rand($fuelStations)],
                    'gallons' => $gallons,
                    'price_per_gallon' => $pricePerGallon,
                    'total_cost' => $totalCost,
                    'odometer_reading' => $odometer,
                    'fuel_type' => 'diesel',
                    'payment_method' => rand(0, 1) ? 'Company Card' : 'Fleet Card',
                    'receipt_number' => 'RCP-' . $date->format('Ymd') . '-' . rand(1000, 9999),
                    'is_personal' => false,
                    'notes' => rand(0, 100) > 80 ? 'DEF also purchased' : null,
                ]
            );
        }
        
        $this->command->info('Created fuel log records.');
    }
    
    private function getRandomIssue(): string
    {
        $issues = [
            'Left front tire low on air pressure',
            'Minor oil leak detected near engine',
            'Windshield wiper blade needs replacement',
            'Brake lights not functioning properly',
            'Turn signal intermittent',
            'Coolant level slightly low',
            'Check engine light on',
            'Air pressure gauge not working',
        ];
        
        return $issues[array_rand($issues)];
    }
    
    private function getRandomPostTripIssue(): string
    {
        $issues = [
            'Debris in trailer',
            'Minor damage to rear bumper',
            'Mud flaps need adjustment',
            'Trailer door latch sticking',
            'Marker light out',
        ];
        
        return $issues[array_rand($issues)];
    }
    
    private function getRandomInspectionNote(): string
    {
        $notes = [
            'All systems checked and operational',
            'Vehicle ready for daily operations',
            'Routine inspection completed',
            'No issues found during inspection',
            'Vehicle in good condition',
            'Pre-trip inspection satisfactory',
            'All safety equipment verified',
        ];
        
        return $notes[array_rand($notes)];
    }
    
    private function getExteriorChecklist($passed = true): array
    {
        return [
            ['item' => 'Lights', 'passed' => true],
            ['item' => 'Reflectors', 'passed' => true],
            ['item' => 'Tires', 'passed' => $passed],
            ['item' => 'Wheels and Rims', 'passed' => true],
            ['item' => 'Windshield', 'passed' => true],
            ['item' => 'Wipers', 'passed' => true],
            ['item' => 'Mirrors', 'passed' => true],
        ];
    }
    
    private function getInteriorChecklist(): array
    {
        return [
            ['item' => 'Gauges', 'passed' => true],
            ['item' => 'Horn', 'passed' => true],
            ['item' => 'Heater/Defroster', 'passed' => true],
            ['item' => 'Seat Belt', 'passed' => true],
            ['item' => 'Emergency Equipment', 'passed' => true],
        ];
    }
    
    private function getEngineChecklist($passed = true): array
    {
        return [
            ['item' => 'Oil Level', 'passed' => true],
            ['item' => 'Coolant Level', 'passed' => $passed],
            ['item' => 'Power Steering Fluid', 'passed' => true],
            ['item' => 'Belts and Hoses', 'passed' => true],
            ['item' => 'Battery', 'passed' => true],
        ];
    }
    
    private function getSafetyChecklist(): array
    {
        return [
            ['item' => 'Parking Brake', 'passed' => true],
            ['item' => 'Service Brakes', 'passed' => true],
            ['item' => 'Air Pressure', 'passed' => true],
            ['item' => 'Brake Lines', 'passed' => true],
        ];
    }
}