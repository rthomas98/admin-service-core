<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\FuelLog;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FuelLog>
 */
class FuelLogFactory extends Factory
{
    protected $model = FuelLog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $fuelStations = [
            'Shell',
            'Exxon',
            'BP',
            'Chevron',
            'Texaco',
            'Pilot Flying J',
            'TA Travel Centers',
            'Loves Travel Stops',
            'Speedway',
            'Circle K',
            'Wawa',
            'QuikTrip',
            'RaceTrac',
            'Sheetz',
            'Casey\'s'
        ];

        $fuelTypes = ['diesel', 'gasoline', 'biodiesel'];
        $paymentMethods = ['company_card', 'credit_card', 'cash', 'fuel_card', 'fleet_card'];
        
        $gallons = $this->faker->numberBetween(15, 200);
        $pricePerGallon = $this->faker->randomFloat(3, 2.50, 5.50);
        $totalCost = $gallons * $pricePerGallon;
        $odometer = $this->faker->numberBetween(10000, 500000);

        return [
            'company_id' => Company::factory(),
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
            'driver_assignment_id' => $this->faker->optional(0.7)->randomElement([null, fn() => DriverAssignment::factory()]),
            'fuel_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'fuel_station' => $this->faker->randomElement($fuelStations),
            'location' => $this->faker->city() . ', ' . $this->faker->stateAbbr(),
            'gallons' => $gallons,
            'price_per_gallon' => $pricePerGallon,
            'total_cost' => round($totalCost, 2),
            'odometer_reading' => $odometer,
            'fuel_type' => $this->faker->randomElement($fuelTypes),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'receipt_number' => $this->faker->optional(0.8)->numerify('RCT-########'),
            'receipt_image' => $this->faker->optional(0.3)->imageUrl(640, 480, 'business'),
            'is_personal' => $this->faker->boolean(5), // 5% chance of personal fuel
            'notes' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    /**
     * Indicate that this is a diesel fuel log.
     */
    public function diesel(): static
    {
        return $this->state(fn (array $attributes) => [
            'fuel_type' => 'diesel',
            'gallons' => $this->faker->numberBetween(50, 200), // Trucks use more fuel
            'price_per_gallon' => $this->faker->randomFloat(3, 3.20, 4.80),
            'fuel_station' => $this->faker->randomElement([
                'Pilot Flying J',
                'TA Travel Centers',
                'Loves Travel Stops',
                'Shell',
                'BP'
            ]),
        ]);
    }

    /**
     * Indicate that this is a gasoline fuel log.
     */
    public function gasoline(): static
    {
        return $this->state(fn (array $attributes) => [
            'fuel_type' => 'gasoline',
            'gallons' => $this->faker->numberBetween(10, 50), // Smaller vehicles
            'price_per_gallon' => $this->faker->randomFloat(3, 2.50, 4.20),
        ]);
    }

    /**
     * Indicate that this is a personal fuel purchase.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_personal' => true,
            'payment_method' => 'cash',
            'gallons' => $this->faker->numberBetween(8, 25),
            'notes' => 'Personal fuel purchase - to be reimbursed',
        ]);
    }

    /**
     * Indicate that this is a business fuel purchase.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_personal' => false,
            'payment_method' => $this->faker->randomElement(['company_card', 'fleet_card', 'fuel_card']),
        ]);
    }

    /**
     * Indicate this is a truck stop fuel purchase.
     */
    public function truckStop(): static
    {
        return $this->state(fn (array $attributes) => [
            'fuel_station' => $this->faker->randomElement([
                'Pilot Flying J',
                'TA Travel Centers',
                'Loves Travel Stops',
                'Petro Stopping Centers',
                'AmBest Travel Centers'
            ]),
            'fuel_type' => 'diesel',
            'gallons' => $this->faker->numberBetween(80, 200),
            'location' => 'Interstate ' . $this->faker->numberBetween(10, 95) . ', ' . $this->faker->city() . ', ' . $this->faker->stateAbbr(),
            'payment_method' => 'fleet_card',
        ]);
    }

    /**
     * Indicate this is a high mileage fuel log.
     */
    public function highMileage(): static
    {
        return $this->state(fn (array $attributes) => [
            'odometer_reading' => $this->faker->numberBetween(200000, 800000),
            'notes' => 'High mileage vehicle - monitoring fuel efficiency',
        ]);
    }

    /**
     * Indicate this fuel log has good fuel efficiency.
     */
    public function efficient(): static
    {
        return $this->state(fn (array $attributes) => [
            'gallons' => $this->faker->numberBetween(15, 80), // Lower fuel consumption
            'notes' => 'Good fuel efficiency recorded',
        ]);
    }

    /**
     * Indicate this is a large fuel purchase.
     */
    public function largePurchase(): static
    {
        $gallons = $this->faker->numberBetween(150, 250);
        $pricePerGallon = $this->faker->randomFloat(3, 3.20, 4.80);

        return $this->state(fn (array $attributes) => [
            'gallons' => $gallons,
            'total_cost' => round($gallons * $pricePerGallon, 2),
            'fuel_type' => 'diesel',
            'fuel_station' => $this->faker->randomElement([
                'Pilot Flying J',
                'TA Travel Centers',
                'Loves Travel Stops'
            ]),
            'payment_method' => 'fleet_card',
            'notes' => 'Large fuel purchase for long haul trip',
        ]);
    }
}