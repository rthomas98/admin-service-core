<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Pricing;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pricing>
 */
class PricingFactory extends Factory
{
    protected $model = Pricing::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $equipmentTypes = [
            'dumpster' => ['10 Yard', '15 Yard', '20 Yard', '30 Yard', '40 Yard'],
            'portable_toilet' => ['Standard', 'Deluxe', 'ADA Compliant', 'VIP Trailer'],
            'roll_off' => ['10 Yard', '20 Yard', '30 Yard', '40 Yard'],
            'compactor' => ['2 Yard', '4 Yard', '6 Yard', '8 Yard'],
            'recycling_bin' => ['Small', 'Medium', 'Large', 'Extra Large'],
            'storage_container' => ['10 ft', '20 ft', '40 ft'],
        ];
        
        $equipmentType = $this->faker->randomKey($equipmentTypes);
        $size = $this->faker->randomElement($equipmentTypes[$equipmentType]);
        
        // Base rates vary by equipment type
        $baseRates = [
            'dumpster' => ['daily' => 50, 'weekly' => 300, 'monthly' => 1000],
            'portable_toilet' => ['daily' => 15, 'weekly' => 75, 'monthly' => 250],
            'roll_off' => ['daily' => 75, 'weekly' => 400, 'monthly' => 1400],
            'compactor' => ['daily' => 100, 'weekly' => 600, 'monthly' => 2000],
            'recycling_bin' => ['daily' => 25, 'weekly' => 150, 'monthly' => 500],
            'storage_container' => ['daily' => 30, 'weekly' => 180, 'monthly' => 600],
        ];
        
        $rates = $baseRates[$equipmentType];
        $dailyRate = $rates['daily'] + $this->faker->randomFloat(2, -10, 50);
        $weeklyRate = $rates['weekly'] + $this->faker->randomFloat(2, -50, 200);
        $monthlyRate = $rates['monthly'] + $this->faker->randomFloat(2, -100, 500);
        
        return [
            'company_id' => Company::inRandomOrder()->first()?->id ?? 1,
            'equipment_type' => $equipmentType,
            'size' => $size,
            'category' => $this->faker->randomElement(['residential', 'commercial', 'industrial', 'construction']),
            'daily_rate' => $dailyRate,
            'weekly_rate' => $weeklyRate,
            'monthly_rate' => $monthlyRate,
            'delivery_fee' => $this->faker->randomFloat(2, 50, 200),
            'pickup_fee' => $this->faker->randomFloat(2, 50, 200),
            'cleaning_fee' => $this->faker->randomFloat(2, 25, 100),
            'maintenance_fee' => $this->faker->randomFloat(2, 0, 150), // Can be 0 but not null
            'damage_fee' => $this->faker->randomFloat(2, 100, 500),
            'late_fee_daily' => $this->faker->randomFloat(2, 10, 50),
            'emergency_surcharge' => $this->faker->randomFloat(2, 100, 300),
            'minimum_rental_days' => $this->faker->numberBetween(1, 7),
            'maximum_rental_days' => $this->faker->optional(0.5)->numberBetween(30, 365),
            'description' => $this->generateDescription($equipmentType, $size),
            'included_services' => $this->generateIncludedServices($equipmentType),
            'additional_charges' => $this->generateAdditionalCharges(),
            'is_active' => true,
            'effective_from' => Carbon::now()->subMonths($this->faker->numberBetween(0, 6)),
            'effective_until' => $this->faker->optional(0.3)->dateTimeBetween('now', '+2 years'),
        ];
    }

    /**
     * Generate a description based on equipment type and size.
     */
    private function generateDescription(string $equipmentType, string $size): string
    {
        $descriptions = [
            'dumpster' => "Perfect for construction debris, household cleanouts, and renovation projects. Our {$size} dumpster provides ample space for your waste disposal needs.",
            'portable_toilet' => "{$size} portable restroom ideal for construction sites, outdoor events, and temporary facilities. Clean, well-maintained, and regularly serviced.",
            'roll_off' => "Heavy-duty {$size} roll-off container suitable for large-scale demolition, construction, and commercial waste management projects.",
            'compactor' => "{$size} commercial compactor for businesses needing efficient waste compression and reduced pickup frequency.",
            'recycling_bin' => "{$size} recycling container for environmentally conscious waste management. Perfect for offices, schools, and residential complexes.",
            'storage_container' => "Secure {$size} storage container for on-site storage needs. Weather-resistant and lockable for maximum security.",
        ];
        
        return $descriptions[$equipmentType] ?? "Quality {$size} {$equipmentType} available for rent.";
    }

    /**
     * Generate included services based on equipment type.
     */
    private function generateIncludedServices(string $equipmentType): array
    {
        $baseServices = ['Delivery to location', 'Pickup after rental period'];
        
        $typeSpecificServices = [
            'dumpster' => ['Weight allowance included', 'Same-day delivery available', 'Permit assistance'],
            'portable_toilet' => ['Weekly cleaning service', 'Toilet paper supplied', 'Hand sanitizer included'],
            'roll_off' => ['Heavy-duty tarp included', 'Safety reflectors', 'Weight tracking'],
            'compactor' => ['Training provided', 'Maintenance included', 'Emergency service'],
            'recycling_bin' => ['Recycling guidance', 'Signage included', 'Monthly reporting'],
            'storage_container' => ['Lock included', 'Ground protection', 'Ventilation system'],
        ];
        
        $services = array_merge($baseServices, $typeSpecificServices[$equipmentType] ?? []);
        
        // Randomly include some additional services
        $additionalServices = [
            '24/7 customer support',
            'Online account management',
            'Flexible rental terms',
            'Insurance options available',
        ];
        
        foreach ($additionalServices as $service) {
            if ($this->faker->boolean(40)) {
                $services[] = $service;
            }
        }
        
        return array_slice($services, 0, $this->faker->numberBetween(3, 6));
    }

    /**
     * Generate additional charges structure.
     */
    private function generateAdditionalCharges(): array
    {
        $charges = [];
        
        if ($this->faker->boolean(60)) {
            $charges['overage_per_ton'] = $this->faker->randomFloat(2, 50, 150);
        }
        
        if ($this->faker->boolean(40)) {
            $charges['weekend_delivery'] = $this->faker->randomFloat(2, 50, 100);
        }
        
        if ($this->faker->boolean(30)) {
            $charges['expedited_service'] = $this->faker->randomFloat(2, 100, 200);
        }
        
        if ($this->faker->boolean(20)) {
            $charges['relocation_fee'] = $this->faker->randomFloat(2, 75, 150);
        }
        
        if ($this->faker->boolean(50)) {
            $charges['environmental_fee'] = $this->faker->randomFloat(2, 15, 50);
        }
        
        return $charges;
    }

    /**
     * Indicate that the pricing is for residential customers.
     */
    public function residential(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'residential',
            'minimum_rental_days' => 1,
            'delivery_fee' => $this->faker->randomFloat(2, 50, 100),
            'pickup_fee' => $this->faker->randomFloat(2, 50, 100),
        ]);
    }

    /**
     * Indicate that the pricing is for commercial customers.
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'commercial',
            'minimum_rental_days' => 7,
            'daily_rate' => $attributes['daily_rate'] * 0.9, // 10% discount
            'weekly_rate' => $attributes['weekly_rate'] * 0.85, // 15% discount
            'monthly_rate' => $attributes['monthly_rate'] * 0.8, // 20% discount
        ]);
    }

    /**
     * Indicate that the pricing is for industrial customers.
     */
    public function industrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'industrial',
            'minimum_rental_days' => 30,
            'maximum_rental_days' => 365,
            'daily_rate' => $attributes['daily_rate'] * 1.2,
            'weekly_rate' => $attributes['weekly_rate'] * 1.15,
            'monthly_rate' => $attributes['monthly_rate'] * 1.1,
            'included_services' => array_merge($attributes['included_services'] ?? [], [
                'Priority service',
                'Dedicated account manager',
                'Custom billing',
            ]),
        ]);
    }

    /**
     * Indicate that the pricing is for construction projects.
     */
    public function construction(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'construction',
            'equipment_type' => $this->faker->randomElement(['dumpster', 'roll_off', 'portable_toilet']),
            'minimum_rental_days' => 7,
            'maximum_rental_days' => 180,
            'included_services' => array_merge($attributes['included_services'] ?? [], [
                'Site safety compliance',
                'Multiple unit discounts',
                'Swap-out service',
            ]),
        ]);
    }

    /**
     * Indicate that the pricing is for a dumpster.
     */
    public function dumpster(string $size = null): static
    {
        $sizes = ['10 Yard', '15 Yard', '20 Yard', '30 Yard', '40 Yard'];
        
        return $this->state(fn (array $attributes) => [
            'equipment_type' => 'dumpster',
            'size' => $size ?? $this->faker->randomElement($sizes),
            'daily_rate' => $this->faker->randomFloat(2, 40, 100),
            'weekly_rate' => $this->faker->randomFloat(2, 250, 500),
            'monthly_rate' => $this->faker->randomFloat(2, 800, 1500),
        ]);
    }

    /**
     * Indicate that the pricing is for a portable toilet.
     */
    public function portableToilet(string $type = null): static
    {
        $types = ['Standard', 'Deluxe', 'ADA Compliant', 'VIP Trailer'];
        
        return $this->state(fn (array $attributes) => [
            'equipment_type' => 'portable_toilet',
            'size' => $type ?? $this->faker->randomElement($types),
            'daily_rate' => $this->faker->randomFloat(2, 10, 30),
            'weekly_rate' => $this->faker->randomFloat(2, 50, 150),
            'monthly_rate' => $this->faker->randomFloat(2, 150, 400),
            'cleaning_fee' => $this->faker->randomFloat(2, 25, 50),
        ]);
    }

    /**
     * Indicate that the pricing is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the pricing is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => Carbon::now()->subYear(),
            'effective_until' => Carbon::now()->subMonth(),
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the pricing is upcoming (future effective date).
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => Carbon::now()->addMonth(),
            'effective_until' => Carbon::now()->addYear(),
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the pricing has no additional fees.
     */
    public function noFees(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_fee' => 0,
            'pickup_fee' => 0,
            'cleaning_fee' => 0,
            'maintenance_fee' => 0,
            'additional_charges' => [],
        ]);
    }

    /**
     * Indicate that the pricing is premium with all services.
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'daily_rate' => $attributes['daily_rate'] * 1.5,
            'weekly_rate' => $attributes['weekly_rate'] * 1.4,
            'monthly_rate' => $attributes['monthly_rate'] * 1.3,
            'included_services' => [
                'White glove delivery',
                'Same-day service',
                '24/7 emergency support',
                'Premium insurance included',
                'Dedicated account manager',
                'Priority scheduling',
                'Free relocations',
                'Damage waiver included',
            ],
            'minimum_rental_days' => 1,
            'maximum_rental_days' => null,
        ]);
    }

    /**
     * Indicate that the pricing is budget-friendly.
     */
    public function budget(): static
    {
        return $this->state(fn (array $attributes) => [
            'daily_rate' => $attributes['daily_rate'] * 0.7,
            'weekly_rate' => $attributes['weekly_rate'] * 0.7,
            'monthly_rate' => $attributes['monthly_rate'] * 0.7,
            'delivery_fee' => 25,
            'pickup_fee' => 25,
            'cleaning_fee' => 15,
            'included_services' => [
                'Basic delivery',
                'Standard pickup',
            ],
            'minimum_rental_days' => 7,
        ]);
    }
}