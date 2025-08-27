<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $items = [];
        $itemCount = $this->faker->numberBetween(1, 4);
        $subtotal = 0;
        
        for ($i = 0; $i < $itemCount; $i++) {
            $price = $this->faker->randomFloat(2, 50, 1000);
            $quantity = $this->faker->numberBetween(1, 5);
            $total = $price * $quantity;
            
            $items[] = [
                'description' => $this->faker->randomElement([
                    '10 Yard Dumpster Rental',
                    '20 Yard Dumpster Rental',
                    '30 Yard Dumpster Rental',
                    'Portable Toilet Rental',
                    'Delivery Service',
                    'Pickup Service',
                    'Monthly Service',
                ]),
                'quantity' => $quantity,
                'price' => $price,
                'total' => $total,
            ];
            
            $subtotal += $total;
        }
        
        $taxRate = 8.5;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $discountAmount = $this->faker->boolean(30) ? $this->faker->randomFloat(2, 10, 100) : 0;
        $totalAmount = $subtotal + $taxAmount - $discountAmount;
        $quoteDate = $this->faker->dateTimeBetween('-1 month', 'now');
        
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'quote_number' => 'Q-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'quote_date' => $quoteDate,
            'valid_until' => Carbon::instance($quoteDate)->addDays(30),
            'items' => json_encode($items),
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
            'status' => 'draft',
            'description' => $this->faker->sentence(),
            'terms_conditions' => "Standard terms and conditions apply.\nQuote valid for 30 days.\nPayment due upon delivery.",
            'notes' => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'delivery_address' => null,
            'delivery_city' => null,
            'delivery_parish' => null,
            'delivery_postal_code' => null,
            'requested_delivery_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'requested_pickup_date' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('+1 month', '+3 months') : null,
            'accepted_date' => null,
            'converted_service_order_id' => null,
        ];
    }

    /**
     * Indicate that the quote is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    /**
     * Indicate that the quote is accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
            'accepted_date' => now()->subDays(rand(1, 5)),
        ]);
    }

    /**
     * Indicate that the quote is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * Indicate that the quote is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'valid_until' => Carbon::now()->subDay(),
        ]);
    }
}