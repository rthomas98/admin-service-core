<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $companyName = $this->faker->company();
        return [
            'name' => $companyName,
            'slug' => \Str::slug($companyName),
            'type' => $this->faker->randomElement(['raw_disposal', 'liv_transport', 'other']),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->streetAddress() . ', ' . $this->faker->city() . ', ' . $this->faker->stateAbbr() . ' ' . $this->faker->postcode(),
            'website' => $this->faker->url(),
            'logo' => null,
            'primary_color' => $this->faker->hexColor(),
            'settings' => [
                'tax_rate' => 8.5,
                'payment_terms' => 'net30',
                'business_hours' => '8:00 AM - 5:00 PM',
            ],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the company is RAW Disposal.
     */
    public function rawDisposal(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'RAW Disposal',
            'slug' => 'raw-disposal',
            'type' => 'raw_disposal',
            'email' => 'info@rawdisposal.com',
            'phone' => '(504) 555-7729',
            'address' => '123 Industrial Blvd, New Orleans, LA 70114',
            'website' => 'https://rawdisposal.com',
            'primary_color' => '#ff6b35',
            'settings' => [
                'tax_rate' => 8.5,
                'payment_terms' => 'net30',
                'business_hours' => 'Monday-Friday: 7:00 AM - 6:00 PM, Saturday: 8:00 AM - 2:00 PM',
                'emergency_phone' => '(504) 555-7729',
                'dispatch_email' => 'dispatch@rawdisposal.com',
                'default_service_areas' => ['Orleans Parish', 'Jefferson Parish'],
            ],
        ]);
    }

    /**
     * Indicate that the company is LIV Transport.
     */
    public function livTransport(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'LIV Transport',
            'slug' => 'liv-transport',
            'type' => 'liv_transport',
            'email' => 'info@livtransport.com',
            'phone' => '(504) 555-5483',
            'address' => '456 Highway 90, New Orleans, LA 70123',
            'website' => 'https://livtransport.com',
            'primary_color' => '#3b82f6',
            'settings' => [
                'tax_rate' => 8.5,
                'payment_terms' => 'net30',
                'business_hours' => '24/7 Operations',
                'dispatch_phone' => '(504) 555-5483',
                'safety_email' => 'safety@livtransport.com',
                'fleet_size' => 50,
                'service_states' => ['LA', 'TX', 'MS', 'AL', 'FL'],
            ],
        ]);
    }

    /**
     * Indicate that the company is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }
}