<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'work_order_id',
        'equipment_id',
        'type',
        'description',
        'quantity',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total',
        'service_date',
        'rental_start_date',
        'rental_end_date',
        'rental_days',
        'metadata',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'service_date' => 'date',
        'rental_start_date' => 'date',
        'rental_end_date' => 'date',
        'metadata' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function calculateTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountAmount = ($this->discount_percent / 100) * $subtotal;
        $afterDiscount = $subtotal - $discountAmount;
        $taxAmount = ($this->tax_rate / 100) * $afterDiscount;

        return $afterDiscount + $taxAmount;
    }

    public static function createFromWorkOrder(WorkOrder $workOrder): array
    {
        $items = [];

        // Add equipment rental charges
        if ($workOrder->equipment) {
            $rentalDays = $workOrder->start_date->diffInDays($workOrder->end_date) ?: 1;

            $items[] = [
                'work_order_id' => $workOrder->id,
                'equipment_id' => $workOrder->equipment_id,
                'type' => 'equipment_rental',
                'description' => "Rental: {$workOrder->equipment->name} - {$workOrder->equipment->type->label()}",
                'quantity' => $rentalDays,
                'unit_price' => $workOrder->equipment->daily_rate ?? $workOrder->equipment->type->defaultDailyRate(),
                'rental_start_date' => $workOrder->start_date,
                'rental_end_date' => $workOrder->end_date,
                'rental_days' => $rentalDays,
                'metadata' => [
                    'equipment_type' => $workOrder->equipment->type->value,
                    'equipment_number' => $workOrder->equipment->equipment_number,
                ],
            ];

            // Add delivery fee if applicable
            if ($workOrder->equipment->delivery_fee > 0) {
                $items[] = [
                    'work_order_id' => $workOrder->id,
                    'equipment_id' => $workOrder->equipment_id,
                    'type' => 'delivery',
                    'description' => 'Delivery Fee',
                    'quantity' => 1,
                    'unit_price' => $workOrder->equipment->delivery_fee,
                ];
            }

            // Add pickup fee if applicable
            if ($workOrder->equipment->pickup_fee > 0) {
                $items[] = [
                    'work_order_id' => $workOrder->id,
                    'equipment_id' => $workOrder->equipment_id,
                    'type' => 'pickup',
                    'description' => 'Pickup Fee',
                    'quantity' => 1,
                    'unit_price' => $workOrder->equipment->pickup_fee,
                ];
            }
        }

        // Add service charges
        if ($workOrder->service_description) {
            $items[] = [
                'work_order_id' => $workOrder->id,
                'type' => 'service',
                'description' => $workOrder->service_description,
                'quantity' => 1,
                'unit_price' => $workOrder->estimated_cost ?? 0,
                'service_date' => $workOrder->scheduled_date,
            ];
        }

        return $items;
    }
}
