<?php

namespace Database\Factories;

use App\Models\ServiceOrder;
use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceOrder>
 */
class ServiceOrderFactory extends Factory
{
    protected $model = ServiceOrder::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $serviceTypes = ['rental', 'delivery_pickup', 'cleaning', 'maintenance', 'emergency'];
        $serviceType = $this->faker->randomElement($serviceTypes);
        $deliveryDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $pickupDate = $this->faker->dateTimeBetween($deliveryDate, '+2 months');
        
        return [
            'company_id' => Company::factory(),
            'customer_id' => Customer::factory(),
            'order_number' => 'SO-' . now()->format('YmdHis') . '-' . $this->faker->unique()->numberBetween(100, 999),
            'service_type' => $serviceType,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled']),
            'delivery_date' => $deliveryDate,
            'delivery_time_start' => '08:00:00',
            'delivery_time_end' => '12:00:00',
            'pickup_date' => $serviceType === 'rental' ? $pickupDate : null,
            'pickup_time_start' => $serviceType === 'rental' ? '08:00:00' : null,
            'pickup_time_end' => $serviceType === 'rental' ? '12:00:00' : null,
            'delivery_address' => $this->faker->streetAddress(),
            'delivery_city' => $this->faker->city(),
            'delivery_parish' => $this->faker->randomElement(['Orleans Parish', 'Jefferson Parish', 'St. Bernard Parish']),
            'delivery_postal_code' => $this->faker->postcode(),
            'pickup_address' => null,
            'pickup_city' => null,
            'pickup_parish' => null,
            'pickup_postal_code' => null,
            'special_instructions' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
            'discount_amount' => $this->faker->boolean(20) ? $this->faker->randomFloat(2, 10, 50) : 0,
            'tax_amount' => null,
            'final_amount' => null,
            'equipment_requested' => json_encode([
                ['type' => 'dumpster', 'size' => '20 yard', 'quantity' => 1]
            ]),
            'notes' => $this->faker->boolean(25) ? $this->faker->sentence() : null,
            'created_at' => Carbon::instance($deliveryDate)->subDays(rand(1, 7)),
        ];
    }

    /**
     * Configure the model factory to calculate tax and total.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (ServiceOrder $order) {
            if ($order->total_amount) {
                $order->tax_amount = round($order->total_amount * 0.085, 2);
                $order->final_amount = $order->total_amount + $order->tax_amount - ($order->discount_amount ?? 0);
            }
        });
    }

    /**
     * Indicate that the service order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the service order is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'delivery_date' => $this->faker->dateTimeBetween('tomorrow', '+1 week'),
        ]);
    }

    /**
     * Indicate that the service order is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'emergency',
            'total_amount' => 500,
            'delivery_date' => today(),
        ]);
    }
}