<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'customer_id',
        'order_number',
        'service_type',
        'status',
        'delivery_date',
        'delivery_time_start',
        'delivery_time_end',
        'pickup_date',
        'pickup_time_start',
        'pickup_time_end',
        'delivery_address',
        'delivery_city',
        'delivery_parish',
        'delivery_postal_code',
        'pickup_address',
        'pickup_city',
        'pickup_parish',
        'pickup_postal_code',
        'special_instructions',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'final_amount',
        'equipment_requested',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivery_date' => 'date',
        'pickup_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'equipment_requested' => 'array',
    ];

    /**
     * Get the company that owns the service order.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the customer for this service order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the equipment assigned to this service order.
     */
    public function equipment(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'service_order_equipment')
            ->withTimestamps();
    }

    /**
     * Get the delivery schedules for this service order.
     */
    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    /**
     * Get the service schedules for this service order.
     */
    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    /**
     * Get the invoices for this service order.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the quote that was converted to this order.
     */
    public function convertedFromQuote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'converted_from_quote_id');
    }

    /**
     * Check if the service order is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'in_progress']);
    }

    /**
     * Check if the service order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the service order is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the delivery address as a formatted string.
     */
    public function getFullDeliveryAddressAttribute(): string
    {
        $parts = array_filter([
            $this->delivery_address,
            $this->delivery_city,
            $this->delivery_parish,
            $this->delivery_postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the pickup address as a formatted string.
     */
    public function getFullPickupAddressAttribute(): string
    {
        $parts = array_filter([
            $this->pickup_address,
            $this->pickup_city,
            $this->pickup_parish,
            $this->pickup_postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Generate a unique order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'SO';
        $date = now()->format('ymd');
        $lastOrder = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? 
            intval(substr($lastOrder->order_number, -3)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
