<?php

namespace Database\Factories;

use App\Enums\FinanceType;
use App\Models\Company;
use App\Models\FinanceCompany;
use App\Models\Vehicle;
use App\Models\Trailer;
use App\Models\VehicleFinance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VehicleFinance>
 */
class VehicleFinanceFactory extends Factory
{
    protected $model = VehicleFinance::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $financeType = $this->faker->randomElement(FinanceType::cases());
        $startDate = $this->faker->dateTimeBetween('-5 years', '-1 year');
        $termMonths = $this->generateTermMonths($financeType);
        $endDate = (clone $startDate)->modify("+{$termMonths} months");
        
        $totalAmount = $this->faker->randomFloat(2, 25000, 200000);
        $downPayment = $financeType === FinanceType::Loan 
            ? $this->faker->randomFloat(2, $totalAmount * 0.1, $totalAmount * 0.3)
            : 0;
        
        $principalAmount = $totalAmount - $downPayment;
        $interestRate = $this->generateInterestRate($financeType);
        $monthlyPayment = $this->calculateMonthlyPayment($principalAmount, $interestRate, $termMonths);

        // Randomly choose financeable type
        $financeableTypes = [Vehicle::class, Trailer::class];
        $financeableType = $this->faker->randomElement($financeableTypes);

        return [
            'company_id' => Company::factory(),
            'financeable_type' => $financeableType,
            'financeable_id' => $financeableType === Vehicle::class ? Vehicle::factory() : Trailer::factory(),
            'finance_company_id' => FinanceCompany::factory(),
            'account_number' => $this->faker->optional(0.8)->numerify('FIN-########'),
            'finance_type' => $financeType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'monthly_payment' => $monthlyPayment,
            'total_amount' => $totalAmount,
            'down_payment' => $downPayment,
            'interest_rate' => $interestRate,
            'term_months' => $termMonths,
            'reference_number' => $this->faker->optional(0.9)->numerify('REF########'),
            'is_active' => $this->faker->boolean(85),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Generate term months based on finance type.
     */
    private function generateTermMonths(FinanceType $financeType): int
    {
        return match ($financeType) {
            FinanceType::Loan => $this->faker->randomElement([36, 48, 60, 72, 84]),
            FinanceType::Lease => $this->faker->randomElement([24, 36, 48, 60]),
            FinanceType::Rental => $this->faker->randomElement([12, 24, 36]),
        };
    }

    /**
     * Generate interest rate based on finance type.
     */
    private function generateInterestRate(FinanceType $financeType): float
    {
        return match ($financeType) {
            FinanceType::Loan => $this->faker->randomFloat(2, 4.5, 12.5),
            FinanceType::Lease => $this->faker->randomFloat(2, 3.5, 8.5),
            FinanceType::Rental => $this->faker->randomFloat(2, 2.5, 6.5),
        };
    }

    /**
     * Calculate monthly payment based on principal, rate, and term.
     */
    private function calculateMonthlyPayment(float $principal, float $interestRate, int $termMonths): float
    {
        if ($interestRate == 0) {
            return round($principal / $termMonths, 2);
        }

        $monthlyRate = $interestRate / 100 / 12;
        $payment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) 
                   / (pow(1 + $monthlyRate, $termMonths) - 1);
        
        return round($payment, 2);
    }

    /**
     * Indicate this is a vehicle finance record.
     */
    public function forVehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'financeable_type' => Vehicle::class,
            'financeable_id' => Vehicle::factory(),
        ]);
    }

    /**
     * Indicate this is a trailer finance record.
     */
    public function forTrailer(): static
    {
        return $this->state(fn (array $attributes) => [
            'financeable_type' => Trailer::class,
            'financeable_id' => Trailer::factory(),
        ]);
    }

    /**
     * Indicate this is a loan.
     */
    public function loan(): static
    {
        $totalAmount = $this->faker->randomFloat(2, 25000, 200000);
        $downPayment = $this->faker->randomFloat(2, $totalAmount * 0.1, $totalAmount * 0.3);
        $termMonths = $this->faker->randomElement([48, 60, 72, 84]);
        $interestRate = $this->faker->randomFloat(2, 4.5, 12.5);
        $monthlyPayment = $this->calculateMonthlyPayment($totalAmount - $downPayment, $interestRate, $termMonths);

        return $this->state(fn (array $attributes) => [
            'finance_type' => FinanceType::Loan,
            'total_amount' => $totalAmount,
            'down_payment' => $downPayment,
            'term_months' => $termMonths,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
        ]);
    }

    /**
     * Indicate this is a lease.
     */
    public function lease(): static
    {
        $totalAmount = $this->faker->randomFloat(2, 15000, 120000);
        $termMonths = $this->faker->randomElement([24, 36, 48]);
        $interestRate = $this->faker->randomFloat(2, 3.5, 8.5);
        $monthlyPayment = $this->calculateMonthlyPayment($totalAmount, $interestRate, $termMonths);

        return $this->state(fn (array $attributes) => [
            'finance_type' => FinanceType::Lease,
            'total_amount' => $totalAmount,
            'down_payment' => 0,
            'term_months' => $termMonths,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'notes' => 'Lease agreement - vehicle must be returned at end of term',
        ]);
    }

    /**
     * Indicate this is a rental.
     */
    public function rental(): static
    {
        $totalAmount = $this->faker->randomFloat(2, 5000, 50000);
        $termMonths = $this->faker->randomElement([12, 24, 36]);
        $interestRate = $this->faker->randomFloat(2, 2.5, 6.5);
        $monthlyPayment = $this->calculateMonthlyPayment($totalAmount, $interestRate, $termMonths);

        return $this->state(fn (array $attributes) => [
            'finance_type' => FinanceType::Rental,
            'total_amount' => $totalAmount,
            'down_payment' => 0,
            'term_months' => $termMonths,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'notes' => 'Short-term rental agreement',
        ]);
    }

    /**
     * Indicate this finance is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => $this->faker->dateTimeBetween('-3 years', '-1 month'),
            'end_date' => $this->faker->dateTimeBetween('+6 months', '+5 years'),
        ]);
    }

    /**
     * Indicate this finance is inactive/completed.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'start_date' => $this->faker->dateTimeBetween('-10 years', '-2 years'),
            'end_date' => $this->faker->dateTimeBetween('-2 years', '-6 months'),
            'notes' => 'Finance completed - ' . $this->faker->randomElement([
                'Paid off early',
                'Full term completed',
                'Refinanced with another lender',
                'Vehicle sold'
            ]),
        ]);
    }

    /**
     * Indicate this finance is expiring soon.
     */
    public function expiring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'end_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'notes' => 'Finance agreement expiring soon - renewal or payoff required',
        ]);
    }

    /**
     * Indicate this finance is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'end_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'notes' => 'Finance agreement overdue - contact finance company immediately',
        ]);
    }

    /**
     * Indicate this is a low interest rate finance.
     */
    public function lowRate(): static
    {
        $financeType = $this->faker->randomElement(FinanceType::cases());
        $interestRate = match ($financeType) {
            FinanceType::Loan => $this->faker->randomFloat(2, 2.5, 5.5),
            FinanceType::Lease => $this->faker->randomFloat(2, 1.5, 4.5),
            FinanceType::Rental => $this->faker->randomFloat(2, 1.0, 3.5),
        };

        return $this->state(fn (array $attributes) => [
            'finance_type' => $financeType,
            'interest_rate' => $interestRate,
            'notes' => 'Promotional low interest rate financing',
        ]);
    }

    /**
     * Indicate this is a high value finance.
     */
    public function highValue(): static
    {
        $totalAmount = $this->faker->randomFloat(2, 150000, 500000);
        $downPayment = $this->faker->randomFloat(2, $totalAmount * 0.15, $totalAmount * 0.35);
        $termMonths = $this->faker->randomElement([72, 84, 96]);
        $interestRate = $this->faker->randomFloat(2, 5.5, 9.5);
        $monthlyPayment = $this->calculateMonthlyPayment($totalAmount - $downPayment, $interestRate, $termMonths);

        return $this->state(fn (array $attributes) => [
            'total_amount' => $totalAmount,
            'down_payment' => $downPayment,
            'term_months' => $termMonths,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'notes' => 'High value equipment financing - commercial grade vehicle/trailer',
        ]);
    }

    /**
     * Indicate this finance has no down payment.
     */
    public function noDownPayment(): static
    {
        return $this->state(fn (array $attributes) => [
            'down_payment' => 0,
            'notes' => 'No down payment financing - 100% financed amount',
        ]);
    }
}