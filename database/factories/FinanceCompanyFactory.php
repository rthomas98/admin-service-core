<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\FinanceCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinanceCompany>
 */
class FinanceCompanyFactory extends Factory
{
    protected $model = FinanceCompany::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $financeCompanies = [
            'Wells Fargo Commercial',
            'Bank of America Commercial Lending',
            'Chase Fleet Services',
            'US Bank Equipment Finance',
            'PNC Equipment Finance',
            'TD Auto Finance',
            'Capital One Auto Finance',
            'Enterprise Fleet Management',
            'Paccar Financial',
            'Volvo Financial Services',
            'Freightliner Financial',
            'International Truck Financial',
            'Ryder System Leasing',
            'Penske Truck Leasing',
            'Budget Truck Rental',
            'Element Fleet Management'
        ];

        $companyName = $this->faker->randomElement($financeCompanies);
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        return [
            'company_id' => Company::factory(),
            'name' => $companyName,
            'account_number' => $this->faker->optional(0.8)->numerify('ACC-########'),
            'contact_name' => $firstName . ' ' . $lastName,
            'phone' => $this->faker->phoneNumber(),
            'email' => strtolower($firstName . '.' . $lastName) . '@' . strtolower(str_replace(' ', '', explode(' ', $companyName)[0])) . '.com',
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip' => $this->faker->postcode(),
            'website' => 'www.' . strtolower(str_replace(' ', '', explode(' ', $companyName)[0])) . '.com',
            'notes' => $this->faker->optional(0.3)->sentence(),
            'is_active' => $this->faker->boolean(90),
        ];
    }

    /**
     * Indicate that the finance company is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'notes' => 'Finance company no longer active - ' . $this->faker->randomElement([
                'Contract expired',
                'Better rates found elsewhere',
                'Service issues',
                'Company policy change',
                'Merger/Acquisition'
            ]),
        ]);
    }

    /**
     * Indicate that this is a major bank.
     */
    public function majorBank(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Wells Fargo Commercial',
                'Bank of America Commercial Lending',
                'Chase Fleet Services',
                'US Bank Equipment Finance',
                'PNC Equipment Finance'
            ]),
            'website' => $this->faker->randomElement([
                'www.wellsfargo.com',
                'www.bankofamerica.com',
                'www.chase.com',
                'www.usbank.com',
                'www.pnc.com'
            ]),
        ]);
    }

    /**
     * Indicate that this is a specialized equipment finance company.
     */
    public function equipmentFinance(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Paccar Financial',
                'Volvo Financial Services',
                'Freightliner Financial',
                'International Truck Financial',
                'Element Fleet Management'
            ]),
            'notes' => 'Specialized in commercial vehicle and equipment financing',
        ]);
    }

    /**
     * Indicate that this is a leasing company.
     */
    public function leasingCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement([
                'Ryder System Leasing',
                'Penske Truck Leasing',
                'Enterprise Fleet Management',
                'Budget Truck Rental',
                'Fleet Pride Leasing'
            ]),
            'notes' => 'Specializes in vehicle leasing and fleet management services',
        ]);
    }

    /**
     * Indicate that this company offers competitive rates.
     */
    public function competitiveRates(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => 'Offers competitive interest rates and flexible terms for commercial vehicles',
            'is_active' => true,
        ]);
    }
}