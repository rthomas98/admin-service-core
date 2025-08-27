<?php

namespace Database\Factories;

use App\Models\EmergencyService;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmergencyService>
 */
class EmergencyServiceFactory extends Factory
{
    protected $model = EmergencyService::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $requestDateTime = $this->faker->dateTimeBetween('-1 month', 'now');
        $requiredByDateTime = Carbon::instance($requestDateTime)->addHours($this->faker->numberBetween(1, 4));
        
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'emergency_number' => EmergencyService::generateEmergencyNumber(),
            'request_datetime' => $requestDateTime,
            'urgency_level' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'emergency_type' => $this->faker->randomElement(['delivery', 'pickup', 'cleaning', 'repair', 'replacement']),
            'description' => $this->faker->sentence(),
            'location_address' => $this->faker->streetAddress(),
            'location_city' => $this->faker->city(),
            'location_parish' => $this->faker->randomElement(['Orleans Parish', 'Jefferson Parish', 'St. Bernard Parish']),
            'location_postal_code' => $this->faker->postcode(),
            'location_latitude' => $this->faker->latitude(),
            'location_longitude' => $this->faker->longitude(),
            'equipment_needed' => json_encode(['Roll-off Truck', 'Dumpster']),
            'required_by_datetime' => $requiredByDateTime,
            'assigned_datetime' => null,
            'dispatched_datetime' => null,
            'arrival_datetime' => null,
            'completion_datetime' => null,
            'target_response_minutes' => $this->faker->numberBetween(30, 240),
            'actual_response_minutes' => null,
            'status' => 'pending',
            'assigned_driver_id' => null,
            'assigned_technician_id' => null,
            'emergency_surcharge' => $this->faker->randomFloat(2, 50, 500),
            'total_cost' => null,
            'completion_notes' => null,
            'photos' => null,
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_name' => $this->faker->name(),
            'special_instructions' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }

    /**
     * Configure the model factory to calculate total cost.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (EmergencyService $service) {
            if (!$service->total_cost) {
                $service->total_cost = $service->emergency_surcharge + $this->faker->randomFloat(2, 200, 1000);
            }
        });
    }

    /**
     * Indicate that the emergency service is completed.
     */
    public function completed(): static
    {
        $requestDateTime = $this->faker->dateTimeBetween('-1 month', '-1 day');
        $assignedDateTime = Carbon::instance($requestDateTime)->addMinutes($this->faker->numberBetween(5, 30));
        $dispatchedDateTime = Carbon::instance($assignedDateTime)->addMinutes($this->faker->numberBetween(5, 20));
        $arrivalDateTime = Carbon::instance($dispatchedDateTime)->addMinutes($this->faker->numberBetween(10, 60));
        $completionDateTime = Carbon::instance($arrivalDateTime)->addMinutes($this->faker->numberBetween(30, 120));
        $actualResponseMinutes = Carbon::instance($requestDateTime)->diffInMinutes($arrivalDateTime);
        
        return $this->state(fn (array $attributes) => [
            'request_datetime' => $requestDateTime,
            'assigned_datetime' => $assignedDateTime,
            'dispatched_datetime' => $dispatchedDateTime,
            'arrival_datetime' => $arrivalDateTime,
            'completion_datetime' => $completionDateTime,
            'actual_response_minutes' => $actualResponseMinutes,
            'status' => 'completed',
            'assigned_driver_id' => Driver::factory(),
            'assigned_technician_id' => User::factory(),
            'completion_notes' => $this->faker->sentence(),
            'emergency_surcharge' => $this->faker->randomFloat(2, 100, 500),
            'total_cost' => $this->faker->randomFloat(2, 500, 2000),
        ]);
    }

    /**
     * Indicate that the emergency service is critical.
     */
    public function critical(): static
    {
        return $this->state(fn (array $attributes) => [
            'urgency_level' => 'critical',
            'target_response_minutes' => 30,
        ]);
    }
}