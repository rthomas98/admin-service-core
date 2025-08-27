<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    protected $model = Equipment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $equipmentTypes = ['dumpster', 'portable_toilet', 'handwash_station', 'holding_tank', 'water_tank'];
        $type = $this->faker->randomElement($equipmentTypes);
        
        // Determine size based on type
        $size = null;
        if ($type === 'dumpster') {
            $size = $this->faker->randomElement(['10 yard', '20 yard', '30 yard', '40 yard']);
        } elseif (in_array($type, ['holding_tank', 'water_tank'])) {
            $size = $this->faker->randomElement(['500 gal', '1000 gal', '2000 gal']);
        }
        
        return [
            'company_id' => Company::factory(),
            'type' => $type,
            'size' => $size,
            'unit_number' => strtoupper($this->faker->unique()->bothify('??-') . now()->format('His') . $this->faker->numberBetween(10, 99)),
            'status' => $this->faker->randomElement(['available', 'rented', 'maintenance', 'retired']),
            'condition' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor']),
            'current_location' => $this->faker->boolean(70) ? $this->faker->address() : 'Yard',
            'latitude' => $this->faker->boolean(30) ? $this->faker->latitude() : null,
            'longitude' => $this->faker->boolean(30) ? $this->faker->longitude() : null,
            'purchase_date' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            'last_service_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'next_service_due' => $this->faker->dateTimeBetween('now', '+3 months'),
            'purchase_price' => $this->faker->randomFloat(2, 1000, 15000),
            'color' => $this->faker->randomElement(['Blue', 'Green', 'Red', 'Yellow', 'Gray']),
            'notes' => $this->faker->boolean(20) ? $this->faker->sentence() : null,
        ];
    }

    /**
     * Indicate that the equipment is a dumpster.
     */
    public function dumpster($size = 20): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'dumpster',
            'size' => "{$size} yard",
        ]);
    }

    /**
     * Indicate that the equipment is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'current_location' => 'Yard',
        ]);
    }

    /**
     * Indicate that the equipment is rented.
     */
    public function rented(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rented',
            'current_location' => $this->faker->address(),
        ]);
    }

    /**
     * Indicate that the equipment needs maintenance.
     */
    public function needsMaintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'condition' => 'poor',
            'next_service_due' => now()->subDays(7),
        ]);
    }
}