<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\Trailer;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DriverAssignment>
 */
class DriverAssignmentFactory extends Factory
{
    protected $model = DriverAssignment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $expectedHours = $this->faker->numberBetween(4, 12);
        $actualHours = $this->faker->optional(0.7)->numberBetween($expectedHours - 2, $expectedHours + 2);
        $mileageStart = $this->faker->numberBetween(10000, 300000);
        $mileageEnd = $actualHours ? $mileageStart + $this->faker->numberBetween(50, 500) : null;

        return [
            'company_id' => Company::factory(),
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'trailer_id' => $this->faker->boolean(60) ? Trailer::factory() : null,
            'start_date' => $startDate,
            'end_date' => $this->faker->boolean(50) ? 
                (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 7) . ' days') : 
                null,
            'status' => $this->faker->randomElement(['scheduled', 'active', 'completed', 'cancelled']),
            'route' => $this->faker->optional(0.8)->streetName() . ' to ' . $this->faker->city(),
            'origin' => $this->faker->address(),
            'destination' => $this->faker->address(),
            'cargo_type' => $this->faker->randomElement([
                'General Freight', 
                'Construction Materials', 
                'Equipment', 
                'Containers', 
                'Machinery',
                'Steel',
                'Lumber',
                'Pipes',
                'Empty Return'
            ]),
            'cargo_weight' => $this->faker->numberBetween(1000, 80000),
            'expected_duration_hours' => $expectedHours,
            'actual_duration_hours' => $actualHours,
            'mileage_start' => $mileageStart,
            'mileage_end' => $mileageEnd,
            'fuel_used' => $actualHours ? $this->faker->numberBetween(20, 150) : null,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the assignment is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'start_date' => $this->faker->dateTimeBetween('+1 day', '+1 week'),
            'end_date' => null,
            'actual_duration_hours' => null,
            'mileage_end' => null,
            'fuel_used' => null,
        ]);
    }

    /**
     * Indicate that the assignment is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-1 day', 'now'),
            'end_date' => null,
            'actual_duration_hours' => null,
            'mileage_end' => null,
        ]);
    }

    /**
     * Indicate that the assignment is completed.
     */
    public function completed(): static
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '-1 day');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 7) . ' days');
        $expectedHours = $this->faker->numberBetween(4, 12);
        $actualHours = $this->faker->numberBetween($expectedHours - 2, $expectedHours + 2);
        $mileageStart = $this->faker->numberBetween(10000, 300000);

        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'actual_duration_hours' => $actualHours,
            'mileage_start' => $mileageStart,
            'mileage_end' => $mileageStart + $this->faker->numberBetween(50, 500),
            'fuel_used' => $this->faker->numberBetween(20, 150),
        ]);
    }

    /**
     * Indicate that the assignment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'end_date' => null,
            'actual_duration_hours' => null,
            'mileage_end' => null,
            'fuel_used' => null,
            'notes' => 'Assignment cancelled - ' . $this->faker->randomElement([
                'Vehicle maintenance required',
                'Driver unavailable',
                'Customer request',
                'Weather conditions',
                'Route changed'
            ]),
        ]);
    }

    /**
     * Indicate that the assignment involves long haul transport.
     */
    public function longHaul(): static
    {
        $mileageStart = $this->faker->numberBetween(10000, 300000);

        return $this->state(fn (array $attributes) => [
            'expected_duration_hours' => $this->faker->numberBetween(8, 16),
            'mileage_start' => $mileageStart,
            'mileage_end' => $mileageStart + $this->faker->numberBetween(300, 800),
            'fuel_used' => $this->faker->numberBetween(80, 200),
            'cargo_type' => $this->faker->randomElement([
                'General Freight',
                'Equipment',
                'Machinery',
                'Steel',
                'Construction Materials'
            ]),
            'cargo_weight' => $this->faker->numberBetween(30000, 80000),
        ]);
    }

    /**
     * Indicate that the assignment is local delivery.
     */
    public function localDelivery(): static
    {
        $mileageStart = $this->faker->numberBetween(10000, 300000);

        return $this->state(fn (array $attributes) => [
            'expected_duration_hours' => $this->faker->numberBetween(2, 6),
            'mileage_start' => $mileageStart,
            'mileage_end' => $mileageStart + $this->faker->numberBetween(20, 150),
            'fuel_used' => $this->faker->numberBetween(10, 50),
            'cargo_type' => $this->faker->randomElement([
                'General Freight',
                'Containers',
                'Construction Materials',
                'Empty Return'
            ]),
            'cargo_weight' => $this->faker->numberBetween(1000, 30000),
        ]);
    }
}