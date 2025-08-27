<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $isOrganization = $this->faker->boolean(40);
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        
        return [
            'company_id' => Company::factory(),
            'external_id' => 'CUST-' . now()->format('YmdHis') . '-' . $this->faker->unique()->numberBetween(100, 999),
            'customer_since' => $this->faker->dateTimeBetween('-5 years', '-1 month'),
            'first_name' => $isOrganization ? null : $firstName,
            'last_name' => $isOrganization ? null : $lastName,
            'name' => $isOrganization ? null : "{$firstName} {$lastName}",
            'organization' => $isOrganization ? $this->faker->company() : null,
            'emails' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'phone_ext' => $this->faker->boolean(10) ? $this->faker->numberBetween(100, 999) : null,
            'secondary_phone' => $this->faker->boolean(30) ? $this->faker->phoneNumber() : null,
            'secondary_phone_ext' => null,
            'fax' => $this->faker->boolean(20) ? $this->faker->phoneNumber() : null,
            'fax_ext' => null,
            'address' => $this->faker->streetAddress(),
            'secondary_address' => $this->faker->boolean(20) ? $this->faker->secondaryAddress() : null,
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip' => $this->faker->postcode(),
            'county' => $this->faker->randomElement(['Orleans Parish', 'Jefferson Parish', 'St. Bernard Parish', 'St. Tammany Parish']),
            'external_message' => $this->faker->boolean(10) ? $this->faker->sentence() : null,
            'internal_memo' => $this->faker->boolean(15) ? $this->faker->sentence() : null,
            'delivery_method' => $this->faker->randomElement(['pickup', 'delivery', 'both']),
            'referral' => $this->faker->randomElement(['website', 'referral', 'advertisement', 'existing_customer', null]),
            'customer_number' => 'CUS' . now()->format('His') . $this->faker->unique()->numberBetween(10, 99),
            'tax_exemption_details' => $this->faker->boolean(10) ? $this->faker->sentence() : null,
            'tax_exempt_reason' => null,
            'divisions' => $this->faker->boolean(20) ? $this->faker->randomElement(['Residential', 'Commercial', 'Industrial']) : null,
            'business_type' => $isOrganization ? $this->faker->randomElement(['Restaurant', 'Retail', 'Office', 'Construction', 'Medical']) : null,
            'tax_code_name' => null,
        ];
    }

    /**
     * Indicate that the customer is commercial.
     */
    public function commercial(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'organization' => $this->faker->company(),
                'first_name' => null,
                'last_name' => null,
                'name' => null,
                'divisions' => 'Commercial',
                'business_type' => $this->faker->randomElement(['Restaurant', 'Retail', 'Office', 'Construction', 'Medical', 'Warehouse']),
            ];
        });
    }

    /**
     * Indicate that the customer is residential.
     */
    public function residential(): static
    {
        return $this->state(function (array $attributes) {
            $firstName = $this->faker->firstName();
            $lastName = $this->faker->lastName();
            
            return [
                'organization' => null,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => "{$firstName} {$lastName}",
                'divisions' => 'Residential',
                'business_type' => null,
            ];
        });
    }

    /**
     * Indicate that the customer is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'external_message' => null,
            'internal_memo' => null,
        ]);
    }

    /**
     * Indicate that the customer is in Orleans Parish.
     */
    public function orleansParish(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'New Orleans',
            'state' => 'LA',
            'county' => 'Orleans Parish',
            'zip' => $this->faker->randomElement(['70112', '70113', '70114', '70115', '70116', '70117', '70118', '70119']),
        ]);
    }

    /**
     * Indicate that the customer is in Jefferson Parish.
     */
    public function jeffersonParish(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => $this->faker->randomElement(['Metairie', 'Kenner', 'Gretna', 'Terrytown', 'Harvey']),
            'state' => 'LA',
            'county' => 'Jefferson Parish',
            'zip' => $this->faker->randomElement(['70001', '70002', '70003', '70004', '70005', '70006', '70056', '70058']),
        ]);
    }
}