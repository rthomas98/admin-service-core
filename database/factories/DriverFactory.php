<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Company;
use App\Models\User;
use App\Enums\DriverStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    protected $model = Driver::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'license_number' => strtoupper($this->faker->unique()->bothify('??######')),
            'license_class' => $this->faker->randomElement(['CDL-A', 'CDL-B', 'Class B', 'Class C']),
            'license_expiry_date' => $this->faker->dateTimeBetween('+6 months', '+3 years'),
            'vehicle_type' => $this->faker->randomElement(['Roll-off Truck', 'Service Truck', 'Flatbed Truck', 'Box Truck']),
            'vehicle_registration' => strtoupper($this->faker->unique()->bothify('???-####')),
            'vehicle_make' => $this->faker->randomElement(['Mack', 'Kenworth', 'Peterbilt', 'Freightliner', 'Ford']),
            'vehicle_model' => $this->faker->randomElement(['CH613', 'T800', '379', 'Cascadia', 'F-550']),
            'vehicle_year' => $this->faker->numberBetween(2015, 2024),
            'service_areas' => [$this->faker->randomElement(['Orleans Parish', 'Jefferson Parish', 'St. Bernard Parish'])],
            'can_lift_heavy' => $this->faker->boolean(80),
            'has_truck_crane' => $this->faker->boolean(40),
            'hourly_rate' => $this->faker->randomFloat(2, 18, 35),
            'shift_start_time' => '06:00:00',
            'shift_end_time' => '16:00:00',
            'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'status' => DriverStatus::ACTIVE,
            'notes' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
            'hired_date' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            // LIV Transport specific fields
            'employee_id' => 'EMP-' . $this->faker->unique()->numerify('####'),
            'emergency_contact' => $this->faker->name(),
            'emergency_phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-21 years'),
            'license_state' => $this->faker->stateAbbr(),
            'medical_card_expiry' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('+1 month', '+2 years') : null,
            'hazmat_expiry' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('+6 months', '+2 years') : null,
            'twic_card_expiry' => $this->faker->boolean(20) ? $this->faker->dateTimeBetween('+1 year', '+5 years') : null,
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip' => $this->faker->postcode(),
            'employment_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract']),
            'drug_test_passed' => true,
            'last_drug_test_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'next_drug_test_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'total_miles_driven' => $this->faker->numberBetween(10000, 500000),
            'safety_score' => $this->faker->numberBetween(75, 100),
            'photo' => null,
            'documents' => [],
        ];
    }

    /**
     * Indicate that the driver is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DriverStatus::INACTIVE,
        ]);
    }

    /**
     * Indicate that the driver is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DriverStatus::SUSPENDED,
        ]);
    }

    /**
     * Indicate that the driver has CDL-A license.
     */
    public function cdlA(): static
    {
        return $this->state(fn (array $attributes) => [
            'license_class' => 'CDL-A',
            'vehicle_type' => 'Roll-off Truck',
            'has_truck_crane' => true,
        ]);
    }

    /**
     * Indicate that the driver works weekends.
     */
    public function worksWeekends(): static
    {
        return $this->state(fn (array $attributes) => [
            'available_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            'hourly_rate' => $this->faker->randomFloat(2, 25, 40),
        ]);
    }
}