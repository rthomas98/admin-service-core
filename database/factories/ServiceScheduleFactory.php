<?php

namespace Database\Factories;

use App\Models\ServiceSchedule;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceSchedule>
 */
class ServiceScheduleFactory extends Factory
{
    protected $model = ServiceSchedule::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $scheduledDateTime = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $estimatedDuration = $this->faker->numberBetween(30, 120);
        
        return [
            'company_id' => Company::factory(),
            'equipment_id' => Equipment::factory(),
            'technician_id' => User::factory(),
            'service_type' => $this->faker->randomElement(['cleaning', 'maintenance', 'repair', 'inspection', 'pump_out']),
            'scheduled_datetime' => $scheduledDateTime,
            'completed_datetime' => null,
            'status' => $this->faker->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
            'priority' => $this->faker->randomElement(['low', 'normal', 'high', 'urgent']),
            'service_description' => $this->faker->sentence(),
            'completion_notes' => null,
            'checklist_items' => json_encode([
                ['item' => 'Check equipment condition', 'completed' => false],
                ['item' => 'Perform service', 'completed' => false],
                ['item' => 'Clean area', 'completed' => false],
            ]),
            'materials_used' => null,
            'service_cost' => $this->faker->randomFloat(2, 50, 500),
            'materials_cost' => 0,
            'total_cost' => null,
            'estimated_duration_minutes' => $estimatedDuration,
            'actual_duration_minutes' => null,
            'requires_followup' => false,
            'followup_date' => null,
            'photos' => null,
        ];
    }

    /**
     * Configure the model factory to calculate total cost.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (ServiceSchedule $schedule) {
            $schedule->total_cost = $schedule->service_cost + $schedule->materials_cost;
        });
    }

    /**
     * Indicate that the service schedule is completed.
     */
    public function completed(): static
    {
        $completedDateTime = $this->faker->dateTimeBetween('-1 month', 'now');
        $actualDuration = $this->faker->numberBetween(25, 150);
        
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'scheduled_datetime' => $completedDateTime,
            'completed_datetime' => Carbon::instance($completedDateTime)->addMinutes($actualDuration),
            'actual_duration_minutes' => $actualDuration,
            'completion_notes' => $this->faker->sentence(),
            'checklist_items' => json_encode([
                ['item' => 'Check equipment condition', 'completed' => true],
                ['item' => 'Perform service', 'completed' => true],
                ['item' => 'Clean area', 'completed' => true],
            ]),
            'materials_used' => json_encode([
                ['material' => 'Cleaning solution', 'quantity' => 1, 'unit' => 'gal'],
            ]),
            'materials_cost' => $this->faker->randomFloat(2, 10, 100),
        ]);
    }

    /**
     * Indicate that the service schedule requires followup.
     */
    public function requiresFollowup(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'requires_followup',
            'requires_followup' => true,
            'followup_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'completion_notes' => 'Additional service required: ' . $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the service schedule is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'service_type' => 'repair',
            'scheduled_datetime' => $this->faker->dateTimeBetween('now', '+2 days'),
        ]);
    }
}