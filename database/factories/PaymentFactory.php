<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = $this->faker->randomFloat(2, 50, 5000);
        $feeAmount = $amount * 0.029; // 2.9% processing fee
        $netAmount = $amount - $feeAmount;
        $paymentDate = $this->faker->dateTimeBetween('-6 months', 'now');
        
        return [
            'company_id' => Company::inRandomOrder()->first()?->id ?? 1,
            'invoice_id' => Invoice::inRandomOrder()->first()?->id ?? 1,
            'customer_id' => Customer::inRandomOrder()->first()?->id ?? 1,
            'amount' => $amount,
            'payment_method' => $this->faker->randomElement(['credit_card', 'check', 'cash', 'bank_transfer', 'paypal']),
            'transaction_id' => 'TXN-' . strtoupper($this->faker->bothify('??##??##')),
            'reference_number' => 'REF-' . $this->faker->numerify('######'),
            'payment_date' => $paymentDate,
            'processed_datetime' => $paymentDate,
            'status' => 'completed',
            'gateway' => null,
            'gateway_transaction_id' => null,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'gateway_response' => null,
            'notes' => $this->faker->optional()->sentence(),
            'check_number' => null,
            'check_date' => null,
            'bank_name' => null,
        ];
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_datetime' => null,
            'gateway_response' => null,
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'gateway_response' => [
                'error' => true,
                'message' => $this->faker->randomElement([
                    'Insufficient funds',
                    'Card declined',
                    'Invalid card number',
                    'Expired card',
                    'Transaction limit exceeded',
                ]),
                'code' => $this->faker->bothify('ERR-####'),
            ],
        ]);
    }

    /**
     * Indicate that the payment is refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'gateway_response' => [
                'refunded' => true,
                'refund_date' => Carbon::now()->toDateTimeString(),
                'refund_amount' => $attributes['amount'],
                'refund_id' => 'REF-' . $this->faker->numerify('######'),
            ],
        ]);
    }

    /**
     * Indicate that the payment is made by credit card.
     */
    public function creditCard(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'credit_card',
            'gateway' => $this->faker->randomElement(['stripe', 'paypal', 'square', 'authorize.net']),
            'gateway_transaction_id' => 'ch_' . $this->faker->bothify('????????????????'),
            'gateway_response' => [
                'success' => true,
                'card_last_four' => $this->faker->numerify('####'),
                'card_brand' => $this->faker->randomElement(['visa', 'mastercard', 'amex', 'discover']),
            ],
        ]);
    }

    /**
     * Indicate that the payment is made by check.
     */
    public function check(): static
    {
        $checkDate = $this->faker->dateTimeBetween('-30 days', 'now');
        
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'check',
            'check_number' => $this->faker->numerify('####'),
            'check_date' => $checkDate,
            'bank_name' => $this->faker->randomElement([
                'Chase Bank',
                'Bank of America',
                'Wells Fargo',
                'Citibank',
                'US Bank',
                'PNC Bank',
                'TD Bank',
                'Capital One',
            ]),
            'fee_amount' => 0, // No fees for checks
            'net_amount' => $attributes['amount'],
        ]);
    }

    /**
     * Indicate that the payment is made by cash.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
            'fee_amount' => 0, // No fees for cash
            'net_amount' => $attributes['amount'],
            'notes' => 'Cash payment received',
        ]);
    }

    /**
     * Indicate that the payment is made by bank transfer.
     */
    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bank_transfer',
            'gateway' => 'ach',
            'gateway_transaction_id' => 'ACH-' . $this->faker->numerify('##########'),
            'fee_amount' => 5.00, // Flat fee for ACH
            'net_amount' => $attributes['amount'] - 5.00,
            'gateway_response' => [
                'success' => true,
                'transfer_type' => 'ACH',
                'account_last_four' => $this->faker->numerify('####'),
            ],
        ]);
    }

    /**
     * Indicate that the payment is made by PayPal.
     */
    public function paypal(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'paypal',
            'gateway' => 'paypal',
            'gateway_transaction_id' => $this->faker->bothify('##??????????##??'),
            'gateway_response' => [
                'success' => true,
                'paypal_email' => $this->faker->email(),
                'payer_id' => $this->faker->bothify('????????'),
            ],
        ]);
    }

    /**
     * Indicate that the payment is partial.
     */
    public function partial(float $percentage = 50): static
    {
        return $this->state(function (array $attributes) use ($percentage) {
            $partialAmount = $attributes['amount'] * ($percentage / 100);
            $feeAmount = $partialAmount * 0.029;
            
            return [
                'amount' => $partialAmount,
                'fee_amount' => $feeAmount,
                'net_amount' => $partialAmount - $feeAmount,
                'notes' => "Partial payment ({$percentage}%)",
            ];
        });
    }

    /**
     * Indicate that the payment is made today.
     */
    public function today(): static
    {
        $today = Carbon::today();
        
        return $this->state(fn (array $attributes) => [
            'payment_date' => $today,
            'processed_datetime' => $today->copy()->addHours($this->faker->numberBetween(0, 23)),
        ]);
    }

    /**
     * Indicate that the payment is made this week.
     */
    public function thisWeek(): static
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        return $this->state(fn (array $attributes) => [
            'payment_date' => $this->faker->dateTimeBetween($startOfWeek, $endOfWeek),
            'processed_datetime' => $this->faker->dateTimeBetween($startOfWeek, $endOfWeek),
        ]);
    }

    /**
     * Indicate that the payment is made this month.
     */
    public function thisMonth(): static
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        return $this->state(fn (array $attributes) => [
            'payment_date' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
            'processed_datetime' => $this->faker->dateTimeBetween($startOfMonth, $endOfMonth),
        ]);
    }

    /**
     * Indicate that the payment is for a large amount.
     */
    public function largeAmount(): static
    {
        $amount = $this->faker->randomFloat(2, 10000, 50000);
        $feeAmount = $amount * 0.029;
        
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'fee_amount' => $feeAmount,
            'net_amount' => $amount - $feeAmount,
        ]);
    }

    /**
     * Indicate that the payment is for a small amount.
     */
    public function smallAmount(): static
    {
        $amount = $this->faker->randomFloat(2, 10, 100);
        $feeAmount = $amount * 0.029;
        
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
            'fee_amount' => $feeAmount,
            'net_amount' => $amount - $feeAmount,
        ]);
    }
}