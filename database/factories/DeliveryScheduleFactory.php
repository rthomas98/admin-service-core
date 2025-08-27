<?php

namespace Database\Factories;

use App\Models\DeliverySchedule;
use App\Models\Company;
use App\Models\ServiceOrder;
use App\Models\Equipment;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliverySchedule>
 */
class DeliveryScheduleFactory extends Factory
{
    protected $model = DeliverySchedule::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $scheduledDateTime = $this->faker->dateTimeBetween('-1 week', '+2 weeks');
        $estimatedDuration = $this->faker->numberBetween(30, 120);
        
        return [
            'company_id' => Company::factory(),
            'service_order_id' => ServiceOrder::factory(),
            'equipment_id' => Equipment::factory(),
            'driver_id' => Driver::factory(),
            'type' => $this->faker->randomElement(['delivery', 'pickup', 'maintenance', 'emergency']),
            'scheduled_datetime' => $scheduledDateTime,
            'actual_datetime' => null,
            'status' => 'scheduled',
            'delivery_address' => $this->faker->streetAddress(),
            'delivery_city' => $this->faker->city(),
            'delivery_parish' => $this->faker->randomElement(['Orleans Parish', 'Jefferson Parish', 'St. Bernard Parish']),
            'delivery_postal_code' => $this->faker->postcode(),
            'delivery_latitude' => $this->faker->latitude(29.8, 30.2),
            'delivery_longitude' => $this->faker->longitude(-90.2, -89.8),
            'delivery_instructions' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'completion_notes' => null,
            'photos' => null,
            'signature' => null,
            'estimated_duration_minutes' => $estimatedDuration,
            'actual_duration_minutes' => null,
            'travel_distance_km' => $this->faker->randomFloat(1, 5, 50),
        ];
    }

    /**
     * Indicate that the delivery schedule is for a delivery.
     */
    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'delivery',
            'delivery_instructions' => $this->faker->randomElement([
                'Gate code: 1234',
                'Call upon arrival',
                'Leave at back entrance',
                'Please park in designated area',
                'Contact site manager before unloading',
            ]),
        ]);
    }

    /**
     * Indicate that the delivery schedule is for a pickup.
     */
    public function pickup(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pickup',
            'delivery_instructions' => $this->faker->randomElement([
                'Equipment is at the back of the property',
                'Please call before arrival',
                'Pickup from loading dock',
                'Container is full and ready',
                'Contact maintenance before pickup',
            ]),
        ]);
    }

    /**
     * Indicate that the delivery schedule is completed.
     */
    public function completed(): static
    {
        $scheduledDateTime = $this->faker->dateTimeBetween('-2 weeks', '-1 day');
        $actualDateTime = Carbon::instance($scheduledDateTime)->addMinutes($this->faker->numberBetween(-30, 30));
        $actualDuration = $this->faker->numberBetween(25, 150);
        
        return $this->state(fn (array $attributes) => [
            'scheduled_datetime' => $scheduledDateTime,
            'actual_datetime' => $actualDateTime,
            'status' => 'completed',
            'completion_notes' => $this->faker->randomElement([
                'Delivery completed successfully',
                'Customer satisfied with service',
                'Placed as instructed',
                'Pickup completed, area cleaned',
                'All items delivered as requested',
            ]),
            'photos' => json_encode([
                'before' => 'delivery_before_' . $this->faker->uuid() . '.jpg',
                'after' => 'delivery_after_' . $this->faker->uuid() . '.jpg',
            ]),
            'signature' => 'signature_' . $this->faker->uuid() . '.png',
            'actual_duration_minutes' => $actualDuration,
        ]);
    }

    /**
     * Indicate that the delivery schedule is en route.
     */
    public function enRoute(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'en_route',
            'scheduled_datetime' => $this->faker->dateTimeBetween('now', '+4 hours'),
        ]);
    }

    /**
     * Indicate that the delivery schedule is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'completion_notes' => $this->faker->randomElement([
                'Customer cancelled',
                'Weather conditions',
                'Equipment not available',
                'Rescheduled by customer',
                'Site not accessible',
            ]),
        ]);
    }

    /**
     * Indicate that the delivery schedule is for today.
     */
    public function today(): static
    {
        $hour = $this->faker->numberBetween(8, 17);
        $minute = $this->faker->randomElement([0, 15, 30, 45]);
        
        return $this->state(fn (array $attributes) => [
            'scheduled_datetime' => Carbon::today()->setTime($hour, $minute),
            'status' => $this->faker->randomElement(['scheduled', 'en_route']),
        ]);
    }

    /**
     * Indicate that the delivery schedule is for tomorrow.
     */
    public function tomorrow(): static
    {
        $hour = $this->faker->numberBetween(8, 17);
        $minute = $this->faker->randomElement([0, 15, 30, 45]);
        
        return $this->state(fn (array $attributes) => [
            'scheduled_datetime' => Carbon::tomorrow()->setTime($hour, $minute),
            'status' => 'scheduled',
        ]);
    }

    /**
     * Indicate that the delivery schedule is for this week.
     */
    public function thisWeek(): static
    {
        $endOfWeek = Carbon::now()->endOfWeek();
        
        return $this->state(fn (array $attributes) => [
            'scheduled_datetime' => $this->faker->dateTimeBetween('now', $endOfWeek),
            'status' => 'scheduled',
        ]);
    }
}