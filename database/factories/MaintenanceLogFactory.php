<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\MaintenanceLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Trailer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceLog>
 */
class MaintenanceLogFactory extends Factory
{
    protected $model = MaintenanceLog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $serviceTypes = [
            'Oil Change',
            'Brake Inspection',
            'Tire Rotation',
            'Engine Repair',
            'Transmission Service',
            'Cooling System',
            'Air Filter Replacement',
            'Battery Replacement',
            'Preventive Maintenance',
            'DOT Inspection',
            'Safety Inspection',
            'Hydraulic Service',
            'Electrical Repair',
            'Suspension Repair',
            'Exhaust System',
            'Fuel System Service',
            'Air Brake Service',
            'Clutch Repair',
            'Differential Service',
            'Emergency Repair'
        ];

        $conditions = ['poor', 'needs_repair', 'fair', 'good', 'excellent'];
        $conditionBefore = $this->faker->randomElement($conditions);
        $conditionAfterOptions = array_slice($conditions, array_search($conditionBefore, $conditions));
        $conditionAfter = $this->faker->randomElement($conditionAfterOptions);

        $serviceCost = $this->faker->randomFloat(2, 50, 2000);
        $partsCost = $this->faker->randomFloat(2, 0, 1500);
        $laborCost = $this->faker->randomFloat(2, 100, 800);
        $totalCost = $serviceCost + $partsCost + $laborCost;

        $serviceDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $startTime = $this->faker->time('H:i:s');
        $endTime = $this->faker->time('H:i:s', strtotime($startTime . ' +' . $this->faker->numberBetween(1, 8) . ' hours'));

        // Randomly choose maintainable type
        $maintainableTypes = [Vehicle::class, Trailer::class, Equipment::class];
        $maintainableType = $this->faker->randomElement($maintainableTypes);

        return [
            'company_id' => Company::factory(),
            'equipment_id' => $maintainableType === Equipment::class ? Equipment::factory() : null,
            'maintainable_type' => $maintainableType,
            'maintainable_id' => $maintainableType === Vehicle::class 
                ? Vehicle::factory() 
                : ($maintainableType === Trailer::class ? Trailer::factory() : Equipment::factory()),
            'technician_id' => User::factory(),
            'driver_id' => $maintainableType === Vehicle::class ? Driver::factory() : null,
            'service_type' => $this->faker->randomElement($serviceTypes),
            'service_date' => $serviceDate,
            'mileage' => $maintainableType === Vehicle::class ? $this->faker->numberBetween(10000, 500000) : null,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'service_cost' => $serviceCost,
            'parts_cost' => $partsCost,
            'labor_cost' => $laborCost,
            'total_cost' => round($totalCost, 2),
            'work_performed' => $this->generateWorkPerformed(),
            'parts_used' => $this->generatePartsUsed(),
            'materials_used' => $this->generateMaterialsUsed(),
            'issues_found' => $this->faker->optional(0.6)->sentence(),
            'recommendations' => $this->faker->optional(0.4)->sentence(),
            'condition_before' => $conditionBefore,
            'condition_after' => $conditionAfter,
            'checklist_completed' => $this->generateChecklist(),
            'photos' => $this->faker->optional(0.3)->randomElements([
                'maintenance_before_1.jpg',
                'maintenance_during_2.jpg',
                'maintenance_after_3.jpg',
                'parts_replaced_4.jpg'
            ], $this->faker->numberBetween(1, 4)),
            'requires_followup' => $this->faker->boolean(20),
            'next_service_date' => $this->faker->optional(0.7)->dateTimeBetween($serviceDate, '+6 months'),
            'notes' => $this->faker->optional(0.3)->paragraph(),
        ];
    }

    /**
     * Generate realistic work performed description.
     */
    private function generateWorkPerformed(): array
    {
        $workItems = [
            'Inspected brake system components',
            'Changed engine oil and filter',
            'Checked tire pressure and tread depth',
            'Tested battery voltage and connections',
            'Inspected belts and hoses',
            'Checked fluid levels',
            'Performed visual safety inspection',
            'Tested lights and electrical systems',
            'Inspected suspension components',
            'Checked exhaust system',
            'Serviced air filter',
            'Lubricated chassis components',
            'Inspected cooling system',
            'Checked steering components'
        ];

        return $this->faker->randomElements($workItems, $this->faker->numberBetween(2, 6));
    }

    /**
     * Generate realistic parts used list.
     */
    private function generatePartsUsed(): array
    {
        $parts = [
            'Oil filter - P5045',
            'Air filter - AF2156',
            'Brake pads - BP1234',
            'Spark plugs (set of 4)',
            'Serpentine belt - SB789',
            'Coolant hose - CH456',
            'Battery - BT12V95',
            'Fuel filter - FF3021',
            'Wiper blades (pair)',
            'Light bulbs (various)',
            'Hydraulic fluid - 5 quarts',
            'Differential oil - 3 quarts',
            'Transmission filter - TF567',
            'Cabin air filter - CAF890'
        ];

        return $this->faker->optional(0.7)->randomElements($parts, $this->faker->numberBetween(1, 4), false) ?? [];
    }

    /**
     * Generate realistic materials used list.
     */
    private function generateMaterialsUsed(): array
    {
        $materials = [
            'Engine oil - 15W40 (15 quarts)',
            'Brake fluid - DOT 3 (1 quart)',
            'Coolant - 50/50 mix (2 gallons)',
            'Grease - Multi-purpose (2 tubes)',
            'Shop rags',
            'Cleaning solvent',
            'Gasket sealer',
            'Thread locker',
            'Penetrating oil',
            'Electrical tape',
            'Wire ties',
            'Safety wire'
        ];

        return $this->faker->optional(0.8)->randomElements($materials, $this->faker->numberBetween(1, 5), false) ?? [];
    }

    /**
     * Generate realistic maintenance checklist.
     */
    private function generateChecklist(): array
    {
        return [
            'pre_inspection' => $this->faker->boolean(95),
            'fluid_levels_checked' => $this->faker->boolean(98),
            'visual_inspection_completed' => $this->faker->boolean(97),
            'safety_systems_tested' => $this->faker->boolean(94),
            'documentation_updated' => $this->faker->boolean(92),
            'post_inspection' => $this->faker->boolean(96),
            'test_drive_completed' => $this->faker->boolean(85),
            'customer_notification' => $this->faker->boolean(88)
        ];
    }

    /**
     * Indicate this is a vehicle maintenance log.
     */
    public function forVehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintainable_type' => Vehicle::class,
            'maintainable_id' => Vehicle::factory(),
            'equipment_id' => null,
            'driver_id' => Driver::factory(),
            'mileage' => $this->faker->numberBetween(10000, 500000),
        ]);
    }

    /**
     * Indicate this is a trailer maintenance log.
     */
    public function forTrailer(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintainable_type' => Trailer::class,
            'maintainable_id' => Trailer::factory(),
            'equipment_id' => null,
            'driver_id' => null,
            'mileage' => null,
        ]);
    }

    /**
     * Indicate this is equipment maintenance log.
     */
    public function forEquipment(): static
    {
        return $this->state(fn (array $attributes) => [
            'maintainable_type' => Equipment::class,
            'maintainable_id' => Equipment::factory(),
            'equipment_id' => Equipment::factory(),
            'driver_id' => null,
            'mileage' => null,
        ]);
    }

    /**
     * Indicate this is preventive maintenance.
     */
    public function preventive(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'Preventive Maintenance',
            'condition_before' => 'good',
            'condition_after' => $this->faker->randomElement(['good', 'excellent']),
            'requires_followup' => false,
            'next_service_date' => $this->faker->dateTimeBetween('+2 months', '+6 months'),
        ]);
    }

    /**
     * Indicate this is emergency repair.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'Emergency Repair',
            'condition_before' => $this->faker->randomElement(['poor', 'needs_repair']),
            'condition_after' => $this->faker->randomElement(['fair', 'good']),
            'requires_followup' => $this->faker->boolean(60),
            'issues_found' => 'Emergency repair required - equipment failure detected',
            'total_cost' => $this->faker->randomFloat(2, 500, 5000),
        ]);
    }

    /**
     * Indicate this maintenance requires followup.
     */
    public function requiresFollowup(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_followup' => true,
            'next_service_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'recommendations' => 'Follow-up service required to monitor repair effectiveness',
        ]);
    }

    /**
     * Indicate this is a DOT inspection.
     */
    public function dotInspection(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'DOT Inspection',
            'maintainable_type' => Vehicle::class,
            'maintainable_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
            'mileage' => $this->faker->numberBetween(10000, 500000),
            'work_performed' => [
                'DOT safety inspection performed',
                'Brake system inspection',
                'Lighting system check',
                'Tire condition assessment',
                'Steering and suspension check',
                'Exhaust system inspection'
            ],
            'checklist_completed' => [
                'dot_brake_inspection' => true,
                'dot_lighting_check' => true,
                'dot_tire_inspection' => true,
                'dot_safety_equipment' => true,
                'dot_documentation' => true,
                'dot_compliance_verified' => true
            ]
        ]);
    }
}