<?php

namespace Database\Factories;

use App\Enums\TrailerType;
use App\Enums\VehicleStatus;
use App\Models\Company;
use App\Models\Trailer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trailer>
 */
class TrailerFactory extends Factory
{
    protected $model = Trailer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $trailerMakes = [
            'Great Dane',
            'Wabash National',
            'Utility Trailer',
            'Hyundai Translead',
            'Stoughton',
            'Dorsey Trailers',
            'Wilson Trailer',
            'Fontaine Trailer',
            'XL Specialized',
            'Manac',
            'Reitnouer',
            'Transcraft',
            'Kaufman Trailers',
            'Landoll',
            'Talbert'
        ];

        $make = $this->faker->randomElement($trailerMakes);
        $year = $this->faker->numberBetween(2010, 2024);
        $type = $this->faker->randomElement(TrailerType::cases());
        
        // Generate specifications based on trailer type
        $specs = $this->generateSpecifications($type);

        return [
            'company_id' => Company::factory(),
            'unit_number' => 'TRL-' . $this->faker->unique()->numerify('####'),
            'vin' => $this->faker->unique()->bothify('1??########??????'),
            'year' => $year,
            'make' => $make,
            'model' => $this->generateModel($make, $type),
            'type' => $type,
            'length' => $specs['length'],
            'width' => $specs['width'],
            'height' => $specs['height'],
            'capacity_weight' => $specs['capacity_weight'],
            'capacity_volume' => $specs['capacity_volume'],
            'license_plate' => strtoupper($this->faker->unique()->bothify('??#####')),
            'registration_state' => $this->faker->stateAbbr(),
            'registration_expiry' => $this->faker->dateTimeBetween('+1 month', '+2 years'),
            'status' => $this->faker->randomElement(VehicleStatus::cases()),
            'purchase_date' => $this->faker->dateTimeBetween('-10 years', '-1 year'),
            'purchase_price' => $this->faker->randomFloat(2, 30000, 150000),
            'purchase_vendor' => $this->faker->company(),
            'is_leased' => $this->faker->boolean(25),
            'lease_end_date' => $this->faker->optional(0.25)->dateTimeBetween('+6 months', '+5 years'),
            'last_inspection_date' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'next_inspection_date' => $this->faker->optional(0.7)->dateTimeBetween('+1 month', '+1 year'),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'image_path' => $this->faker->optional(0.4)->imageUrl(800, 600, 'transport'),
            'specifications' => $this->generateAdditionalSpecs($type),
        ];
    }

    /**
     * Generate specifications based on trailer type.
     */
    private function generateSpecifications(TrailerType $type): array
    {
        return match ($type) {
            TrailerType::Flatbed => [
                'length' => $this->faker->randomElement([48, 53]),
                'width' => 8.5,
                'height' => null,
                'capacity_weight' => $this->faker->numberBetween(48000, 80000),
                'capacity_volume' => null,
            ],
            TrailerType::DryVan => [
                'length' => $this->faker->randomElement([48, 53]),
                'width' => 8.5,
                'height' => $this->faker->randomElement([8.5, 9.0, 9.5]),
                'capacity_weight' => $this->faker->numberBetween(45000, 60000),
                'capacity_volume' => $this->faker->numberBetween(3000, 4000),
            ],
            TrailerType::Refrigerated => [
                'length' => $this->faker->randomElement([48, 53]),
                'width' => 8.5,
                'height' => $this->faker->randomElement([8.5, 9.0]),
                'capacity_weight' => $this->faker->numberBetween(42000, 55000),
                'capacity_volume' => $this->faker->numberBetween(2800, 3800),
            ],
            TrailerType::Tanker => [
                'length' => $this->faker->randomElement([40, 45, 48]),
                'width' => 8.0,
                'height' => 11.0,
                'capacity_weight' => $this->faker->numberBetween(60000, 90000),
                'capacity_volume' => $this->faker->numberBetween(5000, 11000),
            ],
            TrailerType::Lowboy => [
                'length' => $this->faker->randomElement([48, 53, 60]),
                'width' => 8.5,
                'height' => 3.5,
                'capacity_weight' => $this->faker->numberBetween(80000, 150000),
                'capacity_volume' => null,
            ],
            TrailerType::Dump => [
                'length' => $this->faker->randomElement([28, 32, 36]),
                'width' => 8.5,
                'height' => $this->faker->randomElement([5.0, 6.0, 7.0]),
                'capacity_weight' => $this->faker->numberBetween(50000, 80000),
                'capacity_volume' => $this->faker->numberBetween(20, 35),
            ],
            TrailerType::Container => [
                'length' => $this->faker->randomElement([20, 40, 45]),
                'width' => 8.0,
                'height' => 8.5,
                'capacity_weight' => $this->faker->numberBetween(45000, 67000),
                'capacity_volume' => $this->faker->numberBetween(1200, 2400),
            ],
            default => [
                'length' => $this->faker->randomElement([48, 53]),
                'width' => 8.5,
                'height' => 9.0,
                'capacity_weight' => $this->faker->numberBetween(45000, 70000),
                'capacity_volume' => $this->faker->numberBetween(3000, 4000),
            ],
        };
    }

    /**
     * Generate model based on make and type.
     */
    private function generateModel(string $make, TrailerType $type): string
    {
        $models = match ($make) {
            'Great Dane' => ['Champion', 'Everest', 'Freedom', 'Composite Plate Van'],
            'Wabash National' => ['DuraPlate', 'Molded Structural Composite', 'Transcraft Eagle'],
            'Utility Trailer' => ['3000R', '4000D-X', '3000R Reefer'],
            'Hyundai Translead' => ['TL9000', 'TL3000', 'TL5000'],
            'Stoughton' => ['Composite Plate Van', 'Multi-Temp Reefer'],
            'Dorsey Trailers' => ['Pulltarps', 'Intermodal', 'Platform'],
            'Wilson Trailer' => ['Pacesetter', 'Commander', 'Silverstar'],
            default => ['Standard', 'Commercial', 'Heavy Duty'],
        };

        return $this->faker->randomElement($models);
    }

    /**
     * Generate additional specifications.
     */
    private function generateAdditionalSpecs(TrailerType $type): array
    {
        $baseSpecs = [
            'axles' => $this->faker->randomElement([2, 3]),
            'brakes' => 'Air Brakes',
            'suspension' => $this->faker->randomElement(['Air Ride', 'Spring']),
            'tires' => $this->faker->randomElement(['295/75R22.5', '11R22.5', '285/75R24.5']),
        ];

        return match ($type) {
            TrailerType::Refrigerated => array_merge($baseSpecs, [
                'refrigeration_unit' => $this->faker->randomElement(['Thermo King', 'Carrier']),
                'temperature_range' => '-20°F to +70°F',
                'power_source' => 'Diesel Engine',
            ]),
            TrailerType::Tanker => array_merge($baseSpecs, [
                'compartments' => $this->faker->numberBetween(1, 4),
                'material' => $this->faker->randomElement(['Stainless Steel', 'Aluminum', 'Carbon Steel']),
                'baffles' => $this->faker->boolean(80),
            ]),
            TrailerType::Lowboy => array_merge($baseSpecs, [
                'ramps' => $this->faker->randomElement(['Hydraulic', 'Manual', 'Air']),
                'deck_height' => $this->faker->randomElement(['18"', '20"', '24"']),
                'tie_down_points' => $this->faker->numberBetween(12, 24),
            ]),
            TrailerType::Dump => array_merge($baseSpecs, [
                'dump_mechanism' => $this->faker->randomElement(['Hydraulic', 'Electric']),
                'tailgate' => $this->faker->randomElement(['Swing', 'Spread', 'High Lift']),
                'liner' => $this->faker->randomElement(['Steel', 'Aluminum', 'Poly']),
            ]),
            default => $baseSpecs,
        };
    }

    /**
     * Indicate that the trailer is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Active,
        ]);
    }

    /**
     * Indicate that the trailer is in maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => VehicleStatus::Maintenance,
            'notes' => 'Currently undergoing scheduled maintenance',
        ]);
    }

    /**
     * Indicate that the trailer is out of service.
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
     * Indicate that the trailer is leased.
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
     * Indicate that the trailer is owned.
     */
    public function owned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_leased' => false,
            'lease_end_date' => null,
        ]);
    }

    /**
     * Indicate that the trailer is a flatbed.
     */
    public function flatbed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TrailerType::Flatbed,
            'height' => null,
            'capacity_volume' => null,
            'specifications' => array_merge($attributes['specifications'] ?? [], [
                'tie_down_points' => $this->faker->numberBetween(16, 32),
                'side_rails' => $this->faker->boolean(90),
                'rub_rail' => $this->faker->boolean(80),
            ]),
        ]);
    }

    /**
     * Indicate that the trailer is a reefer.
     */
    public function reefer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TrailerType::Refrigerated,
            'specifications' => array_merge($attributes['specifications'] ?? [], [
                'refrigeration_unit' => $this->faker->randomElement(['Thermo King', 'Carrier']),
                'temperature_range' => '-20°F to +70°F',
                'power_source' => 'Diesel Engine',
                'insulation' => 'Polyurethane Foam',
            ]),
        ]);
    }

    /**
     * Indicate that the trailer needs inspection.
     */
    public function needsInspection(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_inspection_date' => $this->faker->dateTimeBetween('-2 years', '-1 year'),
            'next_inspection_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'notes' => 'Inspection overdue - schedule immediately',
        ]);
    }

    /**
     * Indicate that this is a heavy-duty trailer.
     */
    public function heavyDuty(): static
    {
        return $this->state(fn (array $attributes) => [
            'capacity_weight' => $this->faker->numberBetween(80000, 150000),
            'specifications' => array_merge($attributes['specifications'] ?? [], [
                'axles' => 3,
                'heavy_duty_rating' => true,
                'reinforced_frame' => true,
            ]),
        ]);
    }
}