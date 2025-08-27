<?php

namespace Database\Factories;

use App\Models\ServiceArea;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceArea>
 */
class ServiceAreaFactory extends Factory
{
    protected $model = ServiceArea::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {        
        $parishesZips = [
            'Jefferson Parish' => ['70001', '70002', '70003', '70004', '70005'],
            'Orleans Parish' => ['70112', '70113', '70114', '70115', '70116'],
            'St. Bernard Parish' => ['70032', '70043', '70044', '70092', '70085'],
            'St. Tammany Parish' => ['70433', '70435', '70437', '70447', '70448'],
            'Plaquemines Parish' => ['70037', '70040', '70041', '70050', '70083'],
        ];
        
        $parish = $this->faker->randomKey($parishesZips);
        $zipCodes = $parishesZips[$parish];
        
        return [
            'company_id' => Company::factory(),
            'name' => $parish,
            'description' => "Service area covering {$parish} and surrounding areas",
            'zip_codes' => $zipCodes,
            'parishes' => [$parish],
            'boundaries' => null,
            'delivery_surcharge' => $this->faker->randomFloat(2, 10, 50),
            'pickup_surcharge' => $this->faker->randomFloat(2, 10, 50),
            'emergency_surcharge' => $this->faker->randomFloat(2, 50, 200),
            'standard_delivery_days' => $this->faker->numberBetween(1, 3),
            'rush_delivery_hours' => $this->faker->numberBetween(2, 6),
            'rush_delivery_surcharge' => $this->faker->randomFloat(2, 25, 100),
            'is_active' => $this->faker->boolean(90),
            'priority' => $this->faker->numberBetween(1, 10),
            'service_notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
        ];
    }

    /**
     * Indicate that the service area is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the service area is for Orleans Parish.
     */
    public function orleansParish(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Orleans Parish',
            'description' => 'Service area covering Orleans Parish (New Orleans)',
            'zip_codes' => ['70112', '70113', '70114', '70115', '70116', '70117', '70118', '70119'],
            'parishes' => ['Orleans Parish'],
        ]);
    }

    /**
     * Indicate that the service area is for Jefferson Parish.
     */
    public function jeffersonParish(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Jefferson Parish',
            'description' => 'Service area covering Jefferson Parish',
            'zip_codes' => ['70001', '70002', '70003', '70004', '70005', '70006', '70056', '70058'],
            'parishes' => ['Jefferson Parish'],
        ]);
    }
}