<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceOrder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $items = [];
        $itemCount = $this->faker->numberBetween(1, 5);
        $subtotal = 0;
        
        for ($i = 0; $i < $itemCount; $i++) {
            $price = $this->faker->randomFloat(2, 50, 1500);
            $quantity = $this->faker->numberBetween(1, 3);
            $total = $price * $quantity;
            
            $items[] = [
                'description' => $this->faker->randomElement([
                    'Dumpster Rental - 10 Yard',
                    'Dumpster Rental - 20 Yard',
                    'Dumpster Rental - 30 Yard',
                    'Delivery Service',
                    'Pickup Service',
                    'Disposal Fee',
                    'Environmental Fee',
                    'Overage Charge',
                    'Late Fee',
                ]),
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total,
            ];
            
            $subtotal += $total;
        }
        
        $taxRate = 8.5;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalAmount = $subtotal + $taxAmount;
        $invoiceDate = $this->faker->dateTimeBetween('-3 months', 'now');
        
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'service_order_id' => $this->faker->boolean(70) ? ServiceOrder::factory() : null,
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'invoice_date' => $invoiceDate,
            'due_date' => Carbon::instance($invoiceDate)->addDays(30),
            'line_items' => json_encode($items),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $this->faker->boolean(20) ? $this->faker->randomFloat(2, 10, 100) : 0,
            'total_amount' => $totalAmount,
            'amount_paid' => 0,
            'balance_due' => $totalAmount,
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'terms_conditions' => null,
            'billing_address' => null,
            'billing_city' => null,
            'billing_parish' => null,
            'billing_postal_code' => null,
            'is_recurring' => false,
            'recurring_frequency' => null,
            'status' => 'draft',
            'sent_date' => null,
            'paid_date' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Invoice $invoice) {
            if ($invoice->discount_amount > 0) {
                $invoice->total_amount -= $invoice->discount_amount;
                $invoice->balance_due = $invoice->total_amount;
            }
        });
    }

    /**
     * Indicate that the invoice is paid.
     */
    public function paid(): static
    {
        $paidDate = $this->faker->dateTimeBetween('-2 months', 'now');
        
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'amount_paid' => $attributes['total_amount'] ?? 0,
            'balance_due' => 0,
            'invoice_date' => Carbon::instance($paidDate)->subDays(rand(15, 45)),
            'sent_date' => Carbon::instance($paidDate)->subDays(rand(10, 30)),
            'paid_date' => $paidDate,
        ]);
    }

    /**
     * Indicate that the invoice is partially paid.
     */
    public function partiallyPaid(): static
    {
        return $this->state(function (array $attributes) {
            $partial = round($attributes['total_amount'] * 0.5, 2);
            return [
                'status' => 'partial',
                'amount_paid' => $partial,
                'balance_due' => $attributes['total_amount'] - $partial,
                'sent_at' => now()->subDays(rand(20, 40)),
            ];
        });
    }

    /**
     * Indicate that the invoice is overdue.
     */
    public function overdue(): static
    {
        $invoiceDate = now()->subDays(60);
        
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'invoice_date' => $invoiceDate,
            'due_date' => Carbon::instance($invoiceDate)->addDays(30),
            'sent_at' => $invoiceDate->copy()->addDay(),
            'overdue_at' => $invoiceDate->copy()->addDays(31),
        ]);
    }
}