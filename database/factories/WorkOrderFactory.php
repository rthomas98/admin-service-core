<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\ServiceOrder;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderAction;
use App\Enums\TimePeriod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceDate = $this->faker->dateTimeBetween('-30 days', '+7 days');
        $timeOnSite = $this->faker->time('H:i');
        $timeOffSite = Carbon::parse($timeOnSite)->addMinutes($this->faker->numberBetween(15, 120))->format('H:i');
        
        $containerSizes = ['10 Yard', '15 Yard', '20 Yard', '30 Yard', '40 Yard'];
        $wasteTypes = [
            'Construction Debris',
            'Household Waste',
            'Yard Waste',
            'Concrete/Asphalt',
            'Mixed Waste',
            'Roofing Materials',
            'Demolition Waste',
            'Recyclables',
            'Clean Wood',
            'Scrap Metal',
        ];
        
        $status = $this->faker->randomElement(WorkOrderStatus::cases());
        $action = $this->faker->randomElement(WorkOrderAction::cases());
        
        // Get related models
        $company = Company::inRandomOrder()->first();
        $customer = Customer::where('company_id', $company?->id ?? 1)->inRandomOrder()->first();
        $driver = Driver::where('company_id', $company?->id ?? 1)->inRandomOrder()->first();
        
        $isCompleted = $status === WorkOrderStatus::COMPLETED;
        $requiresCOD = $this->faker->boolean(15);
        
        return [
            'company_id' => $company?->id ?? 1,
            'ticket_number' => $this->generateTicketNumber(),
            'po_number' => $this->faker->optional(0.3)->numerify('PO-####'),
            'service_date' => $serviceDate,
            'time_on_site' => $timeOnSite,
            'time_off_site' => $timeOffSite,
            'time_on_site_period' => Carbon::parse($timeOnSite)->format('A') === 'AM' ? TimePeriod::AM : TimePeriod::PM,
            'time_off_site_period' => Carbon::parse($timeOffSite)->format('A') === 'AM' ? TimePeriod::AM : TimePeriod::PM,
            'truck_number' => $this->faker->numerify('T-###'),
            'dispatch_number' => $this->faker->numerify('DISP-######'),
            'driver_id' => $driver?->id,
            'customer_id' => $customer?->id,
            'customer_name' => $customer ? $customer->name : $this->faker->company(),
            'address' => $customer?->address ?? $this->faker->streetAddress(),
            'city' => $customer?->city ?? $this->faker->city(),
            'state' => $customer?->state ?? $this->faker->stateAbbr(),
            'zip' => $customer?->zip ?? $this->faker->postcode(),
            'action' => $action,
            'container_size' => $this->faker->randomElement($containerSizes),
            'waste_type' => $this->faker->randomElement($wasteTypes),
            'service_description' => $this->generateServiceDescription($action),
            'container_delivered' => $action === WorkOrderAction::DELIVERY ? $this->faker->numerify('CONT-#####') : null,
            'container_picked_up' => $action === WorkOrderAction::PICKUP ? $this->faker->numerify('CONT-#####') : null,
            'disposal_id' => $isCompleted && $action === WorkOrderAction::PICKUP ? $this->faker->numerify('DISP-#####') : null,
            'disposal_ticket' => $isCompleted && $action === WorkOrderAction::PICKUP ? $this->faker->numerify('TICK-#######') : null,
            'cod_amount' => $requiresCOD ? $this->faker->randomFloat(2, 100, 1500) : 0,
            'cod_signature' => $requiresCOD && $isCompleted ? $this->faker->name() : null,
            'comments' => $this->faker->optional(0.3)->sentence(),
            'customer_signature' => $isCompleted ? $this->faker->name() : null,
            'customer_signature_date' => $isCompleted ? Carbon::parse($serviceDate)->addHours($this->faker->numberBetween(1, 4)) : null,
            'driver_signature' => $isCompleted ? ($driver ? $driver->first_name . ' ' . $driver->last_name : $this->faker->name()) : null,
            'driver_signature_date' => $isCompleted ? Carbon::parse($serviceDate)->addHours($this->faker->numberBetween(1, 4)) : null,
            'status' => $status,
            'completed_at' => $isCompleted ? Carbon::parse($serviceDate)->addHours($this->faker->numberBetween(2, 6)) : null,
            'service_order_id' => ServiceOrder::where('company_id', $company?->id ?? 1)
                ->where('customer_id', $customer?->id)
                ->inRandomOrder()
                ->first()?->id,
        ];
    }

    /**
     * Generate a unique ticket number.
     */
    private function generateTicketNumber(): string
    {
        return 'WO-' . date('Y') . '-' . str_pad($this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate service description based on action.
     */
    private function generateServiceDescription(WorkOrderAction $action): string
    {
        $descriptions = [
            WorkOrderAction::DELIVERY->value => [
                'Deliver dumpster to customer location',
                'New container delivery for construction project',
                'Scheduled delivery for residential cleanout',
                'Container placement for renovation project',
                'Emergency delivery request fulfilled',
            ],
            WorkOrderAction::PICKUP->value => [
                'Scheduled pickup of filled container',
                'End of rental period pickup',
                'Customer requested early pickup',
                'Container full - ready for disposal',
                'Final pickup and site cleanup',
            ],
            WorkOrderAction::SERVICE->value => [
                'Container swap - full for empty',
                'Regular maintenance service',
                'Container inspection and cleaning',
                'Relocate container on property',
                'General service and maintenance',
            ],
            WorkOrderAction::EMERGENCY->value => [
                'Emergency container delivery',
                'Urgent pickup request',
                'Emergency overflow response',
                'Critical waste removal',
                'After-hours emergency service',
            ],
            WorkOrderAction::OTHER->value => [
                'Special customer request',
                'Administrative service',
                'Site assessment',
                'Documentation update',
                'Customer consultation',
            ],
        ];

        $actionValue = $action->value;
        return $this->faker->randomElement($descriptions[$actionValue] ?? ['Service completed as requested']);
    }

    /**
     * Indicate that the work order is draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrderStatus::DRAFT,
            'completed_at' => null,
            'customer_signature' => null,
            'customer_signature_date' => null,
            'driver_signature' => null,
            'driver_signature_date' => null,
            'disposal_id' => null,
            'disposal_ticket' => null,
        ]);
    }

    /**
     * Indicate that the work order is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrderStatus::IN_PROGRESS,
            'completed_at' => null,
            'customer_signature' => null,
            'customer_signature_date' => null,
            'driver_signature' => null,
            'driver_signature_date' => null,
        ]);
    }

    /**
     * Indicate that the work order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrderStatus::COMPLETED,
            'completed_at' => now()->subDays($this->faker->numberBetween(0, 30)),
            'customer_signature' => $this->faker->name(),
            'customer_signature_date' => now()->subDays($this->faker->numberBetween(0, 30)),
            'driver_signature' => $this->faker->name(),
            'driver_signature_date' => now()->subDays($this->faker->numberBetween(0, 30)),
        ]);
    }

    /**
     * Indicate that the work order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrderStatus::CANCELLED,
            'completed_at' => null,
            'comments' => 'Cancelled: ' . $this->faker->randomElement([
                'Customer no longer needs service',
                'Weather conditions',
                'Site not accessible',
                'Customer requested cancellation',
                'Scheduling conflict',
            ]),
        ]);
    }

    /**
     * Indicate that the work order is for delivery.
     */
    public function delivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => WorkOrderAction::DELIVERY,
            'container_delivered' => $this->faker->numerify('CONT-#####'),
            'container_picked_up' => null,
            'service_description' => $this->generateServiceDescription(WorkOrderAction::DELIVERY),
        ]);
    }

    /**
     * Indicate that the work order is for pickup.
     */
    public function pickup(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => WorkOrderAction::PICKUP,
            'container_delivered' => null,
            'container_picked_up' => $this->faker->numerify('CONT-#####'),
            'service_description' => $this->generateServiceDescription(WorkOrderAction::PICKUP),
        ]);
    }

    /**
     * Indicate that the work order is for swap/service.
     */
    public function swap(): static
    {
        $containerNumber = $this->faker->numerify('CONT-#####');
        return $this->state(fn (array $attributes) => [
            'action' => WorkOrderAction::SERVICE,
            'container_delivered' => $containerNumber,
            'container_picked_up' => $containerNumber,
            'service_description' => 'Container swap - full for empty',
        ]);
    }

    /**
     * Indicate that the work order is for service.
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => WorkOrderAction::SERVICE,
            'service_description' => $this->generateServiceDescription(WorkOrderAction::SERVICE),
        ]);
    }

    /**
     * Indicate that the work order is for emergency.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => WorkOrderAction::EMERGENCY,
            'service_description' => $this->generateServiceDescription(WorkOrderAction::EMERGENCY),
        ]);
    }

    /**
     * Indicate that the work order requires COD.
     */
    public function withCOD(): static
    {
        return $this->state(fn (array $attributes) => [
            'cod_amount' => $this->faker->randomFloat(2, 200, 2000),
            'cod_signature' => $attributes['status'] === WorkOrderStatus::COMPLETED ? $this->faker->name() : null,
        ]);
    }

    /**
     * Indicate that the work order is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_date' => today(),
            'status' => $this->faker->randomElement([WorkOrderStatus::DRAFT, WorkOrderStatus::IN_PROGRESS]),
        ]);
    }

    /**
     * Indicate that the work order is for this week.
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_date' => $this->faker->dateTimeBetween('now', 'this week Friday'),
            'status' => WorkOrderStatus::DRAFT,
        ]);
    }

    /**
     * Indicate that the work order is for a specific customer.
     */
    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'address' => $customer->address,
            'city' => $customer->city,
            'state' => $customer->state,
            'zip' => $customer->zip,
        ]);
    }

    /**
     * Indicate that the work order is for a specific driver.
     */
    public function forDriver(Driver $driver): static
    {
        return $this->state(fn (array $attributes) => [
            'driver_id' => $driver->id,
            'driver_signature' => $attributes['status'] === WorkOrderStatus::COMPLETED 
                ? $driver->first_name . ' ' . $driver->last_name 
                : null,
        ]);
    }

    /**
     * Indicate that the work order is for construction waste.
     */
    public function constructionWaste(): static
    {
        return $this->state(fn (array $attributes) => [
            'waste_type' => $this->faker->randomElement([
                'Construction Debris',
                'Demolition Waste',
                'Roofing Materials',
                'Concrete/Asphalt',
                'Drywall',
            ]),
            'container_size' => $this->faker->randomElement(['20 Yard', '30 Yard', '40 Yard']),
        ]);
    }

    /**
     * Indicate that the work order is for residential waste.
     */
    public function residentialWaste(): static
    {
        return $this->state(fn (array $attributes) => [
            'waste_type' => $this->faker->randomElement([
                'Household Waste',
                'Yard Waste',
                'Mixed Waste',
                'Furniture',
                'Appliances',
            ]),
            'container_size' => $this->faker->randomElement(['10 Yard', '15 Yard', '20 Yard']),
        ]);
    }
}