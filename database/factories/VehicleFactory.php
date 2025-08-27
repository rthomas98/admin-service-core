<?php

namespace Database\Factories;

use App\Enums\FuelType;
use App\Enums\VehicleStatus;
use App\Enums\VehicleType;
use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $vehicleMakes = [
            'Freightliner', 'Kenworth', 'Peterbilt', 'Mack', 'International',
            'Volvo', 'Western Star', 'Ford', 'Chevrolet', 'Ram',
            'Isuzu', 'Hino', 'Mitsubishi Fuso', 'Mercedes-Benz'
        ];

        $make = $this->faker->randomElement($vehicleMakes);
        $year = $this->faker->numberBetween(2010, 2024);
        $type = $this->faker->randomElement(VehicleType::cases());
        $fuelType = $this->faker->randomElement(FuelType::cases());
        
        // Generate specifications based on vehicle type
        $specs = $this->generateSpecifications($make, $type, $fuelType);

        return [
            'company_id' => Company::factory(),
            'unit_number' => 'VEH-' . $this->faker->unique()->numerify('####'),
            'vin' => $this->faker->unique()->bothify('1??########??????'),
            'year' => $year,
            'make' => $make,
            'model' => $this->generateModel($make, $type),
            'type' => $type,
            'color' => $this->faker->randomElement([
                'White', 'Black', 'Silver', 'Gray', 'Blue', 'Red', 
                'Green', 'Yellow', 'Orange', 'Brown'
            ]),
            'license_plate' => strtoupper($this->faker->unique()->bothify('??#####')),
            'registration_state' => $this->faker->stateAbbr(),
            'registration_expiry' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
            'odometer' => $this->faker->numberBetween(10000, 500000),
            'odometer_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'fuel_capacity' => $specs['fuel_capacity'],
            'fuel_type' => $fuelType,
            'status' => $this->faker->randomElement(VehicleStatus::cases()),
            'purchase_date' => $this->faker->dateTimeBetween('-10 years', '-1 year'),
            'purchase_price' => $this->faker->randomFloat(2, 25000, 200000),
            'purchase_vendor' => $this->faker->company(),
            'is_leased' => $this->faker->boolean(25),
            'lease_end_date' => $this->faker->optional(0.25)->dateTimeBetween('+6 months', '+5 years'),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'image_path' => $this->faker->optional(0.4)->imageUrl(800, 600, 'transport'),
            'specifications' => $this->generateAdditionalSpecs($make, $type, $fuelType),
        ];
    }

    /**
     * Generate specifications based on vehicle type and make.
     */
    private function generateSpecifications(string $make, VehicleType $type, FuelType $fuelType): array
    {
        return match ($type) {
            VehicleType::Truck => [
                'fuel_capacity' => match ($make) {
                    'Freightliner', 'Kenworth', 'Peterbilt', 'Mack' => $this->faker->randomElement([120, 150, 200, 300]),
                    'International', 'Volvo', 'Western Star' => $this->faker->randomElement([100, 150, 200]),
                    default => $this->faker->randomElement([80, 100, 120]),
                },
            ],
            VehicleType::Van => [
                'fuel_capacity' => $this->faker->randomElement([24, 30, 36, 40]),
            ],
            VehicleType::Pickup => [
                'fuel_capacity' => $this->faker->randomElement([20, 26, 30, 36]),
            ],
            VehicleType::SUV => [
                'fuel_capacity' => $this->faker->randomElement([18, 22, 26, 30]),
            ],
            VehicleType::Car => [
                'fuel_capacity' => $this->faker->randomElement([12, 15, 18, 20]),
            ],
            default => [
                'fuel_capacity' => $this->faker->randomElement([20, 30, 40, 50]),
            ],
        };
    }

    /**
     * Generate model based on make and type.
     */
    private function generateModel(string $make, VehicleType $type): string
    {
        $models = match ($make) {
            'Freightliner' => ['Cascadia', 'Columbia', 'Coronado', 'Century Class', 'Sprinter'],
            'Kenworth' => ['T680', 'T880', 'W900', 'T800', 'T370'],
            'Peterbilt' => ['579', '389', '567', '348', '220'],
            'Mack' => ['Anthem', 'Granite', 'Pinnacle', 'TerraPro', 'LR'],
            'International' => ['LT', 'RH', 'HX', 'MV', 'CV'],
            'Volvo' => ['VNL', 'VHD', 'VAH', 'VNR', 'FE'],
            'Western Star' => ['5700XE', '4700', '4900', '6900XD', '47X'],
            'Ford' => ['F-150', 'F-250', 'F-350', 'F-450', 'F-550', 'Transit', 'E-Series'],
            'Chevrolet' => ['Silverado 1500', 'Silverado 2500HD', 'Silverado 3500HD', 'Express'],
            'Ram' => ['1500', '2500', '3500', '4500', '5500', 'ProMaster'],
            'Isuzu' => ['NPR', 'NQR', 'NRR', 'FTR', 'FVR'],
            'Hino' => ['155', '195', '268', '338', '358'],
            'Mitsubishi Fuso' => ['Canter', 'Fighter', 'Super Great'],
            'Mercedes-Benz' => ['Sprinter', 'Metris', 'Actros', 'Atego'],
            default => ['Standard', 'Commercial', 'Heavy Duty'],
        };

        return $this->faker->randomElement($models);
    }

    /**
     * Generate additional specifications.
     */
    private function generateAdditionalSpecs(string $make, VehicleType $type, FuelType $fuelType): array
    {
        $baseSpecs = [
            'transmission' => $this->faker->randomElement(['Manual', 'Automatic', '10-Speed Manual', '12-Speed Automated']),
            'engine_size' => $this->generateEngineSize($type, $fuelType),
            'drivetrain' => $this->faker->randomElement(['2WD', '4WD', 'AWD']),
        ];

        return match ($type) {
            VehicleType::Truck => array_merge($baseSpecs, [
                'sleeper_cab' => $this->faker->boolean(60),
                'axle_configuration' => $this->faker->randomElement(['6x4', '6x2', '4x2']),
                'gross_weight' => $this->faker->numberBetween(26001, 80000),
                'towing_capacity' => $this->faker->numberBetween(20000, 80000),
            ]),
            VehicleType::Van => array_merge($baseSpecs, [
                'cargo_volume' => $this->faker->numberBetween(250, 500),
                'passenger_capacity' => $this->faker->numberBetween(2, 15),
                'roof_height' => $this->faker->randomElement(['Standard', 'High', 'Extended']),
            ]),
            VehicleType::Pickup => array_merge($baseSpecs, [
                'bed_length' => $this->faker->randomElement(['Short Bed', 'Standard Bed', 'Long Bed']),
                'cab_style' => $this->faker->randomElement(['Regular', 'Extended', 'Crew']),
                'towing_capacity' => $this->faker->numberBetween(3000, 15000),
            ]),
            default => $baseSpecs,
        };
    }

    /**
     * Generate engine size based on type and fuel type.
     */
    private function generateEngineSize(VehicleType $type, FuelType $fuelType): string
    {
        return match ($type) {
            VehicleType::Truck => match ($fuelType) {
                FuelType::Diesel => $this->faker->randomElement(['12.8L', '14.9L', '15.6L', '16.0L']),
                FuelType::Gasoline => $this->faker->randomElement(['6.0L', '6.2L', '6.6L', '7.4L']),
                default => '12.8L',
            },
            VehicleType::Van => match ($fuelType) {
                FuelType::Diesel => $this->faker->randomElement(['2.1L', '3.0L', '3.5L']),
                FuelType::Gasoline => $this->faker->randomElement(['3.6L', '4.6L', '5.4L', '6.8L']),
                default => '3.6L',
            },
            VehicleType::Pickup => match ($fuelType) {
                FuelType::Diesel => $this->faker->randomElement(['3.0L', '6.7L', '6.6L']),
                FuelType::Gasoline => $this->faker->randomElement(['5.3L', '5.7L', '6.2L', '6.6L']),
                default => '5.3L',
            },
            default => match ($fuelType) {
                FuelType::Diesel => '3.0L',
                FuelType::Gasoline => '3.6L',
                default => '2.4L',
            },
        };
    }

    /**
     * Indicate that the vehicle is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Active,
        ]);
    }

    /**
     * Indicate that the vehicle is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Maintenance,
            'notes' => 'Currently undergoing scheduled maintenance',
        ]);
    }

    /**
     * Indicate that the vehicle is out of service.
     */
    public function outOfService(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::OutOfService,
            'notes' => 'Out of service - ' . $this->faker->randomElement([
                'Awaiting repairs',
                'Insurance claim pending',
                'DOT inspection required',
                'Awaiting parts'
            ]),
        ]);
    }

    /**
     * Indicate that the vehicle is leased.
     */
    public function leased(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_leased' => true,
            'lease_end_date' => $this->faker->dateTimeBetween('+6 months', '+5 years'),
            'purchase_price' => null,
            'notes' => 'Leased vehicle - monthly payments apply',
        ]);
    }

    /**
     * Indicate that the vehicle is owned.
     */
    public function owned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_leased' => false,
            'lease_end_date' => null,
        ]);
    }

    /**
     * Indicate that the vehicle is a truck.
     */
    public function truck(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => VehicleType::Truck,
            'make' => $this->faker->randomElement([
                'Freightliner', 'Kenworth', 'Peterbilt', 'Mack', 'International', 'Volvo'
            ]),
            'fuel_type' => FuelType::Diesel,
            'fuel_capacity' => $this->faker->randomElement([120, 150, 200, 300]),
        ]);
    }

    /**
     * Indicate that the vehicle is diesel-powered.
     */
    public function diesel(): static
    {
        return $this->state(fn (array $attributes) => [
            'fuel_type' => FuelType::Diesel,
        ]);
    }

    /**
     * Indicate that the vehicle is gasoline-powered.
     */
    public function gasoline(): static
    {
        return $this->state(fn (array $attributes) => [
            'fuel_type' => FuelType::Gasoline,
        ]);
    }

    /**
     * Indicate that the vehicle has high mileage.
     */
    public function highMileage(): static
    {
        return $this->state(fn (array $attributes) => [
            'odometer' => $this->faker->numberBetween(200000, 800000),
            'notes' => 'High mileage vehicle - monitor maintenance schedule closely',
        ]);
    }

    /**
     * Indicate that the vehicle is new/recent model.
     */
    public function newVehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => $this->faker->numberBetween(2022, 2024),
            'odometer' => $this->faker->numberBetween(0, 15000),
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ]);
    }

    /**
     * Indicate that the vehicle needs registration renewal.
     */
    public function needsRegistration(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_expiry' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'notes' => 'Registration expiring soon - renewal required',
        ]);
    }
}