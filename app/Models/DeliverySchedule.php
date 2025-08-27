<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DeliverySchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'service_order_id',
        'equipment_id',
        'driver_id',
        'type',
        'scheduled_datetime',
        'actual_datetime',
        'status',
        'delivery_address',
        'delivery_city',
        'delivery_parish',
        'delivery_postal_code',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_instructions',
        'completion_notes',
        'photos',
        'signature',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'travel_distance_km',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_datetime' => 'datetime',
        'actual_datetime' => 'datetime',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'photos' => 'array',
        'travel_distance_km' => 'decimal:2',
    ];

    /**
     * Get the company that owns the delivery schedule.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the service order for this delivery schedule.
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Get the equipment for this delivery schedule.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the driver assigned to this delivery schedule.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Check if delivery is scheduled for today.
     */
    public function isToday(): bool
    {
        return $this->scheduled_datetime->isToday();
    }

    /**
     * Check if delivery is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_datetime->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if delivery is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the full delivery address as a formatted string.
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
     * Get the duration in hours and minutes format.
     */
    public function getFormattedDurationAttribute(): ?string
    {
        $minutes = $this->actual_duration_minutes ?? $this->estimated_duration_minutes;
        
        if (!$minutes) {
            return null;
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $remainingMinutes . 'm';
        }

        return $remainingMinutes . 'm';
    }

    /**
     * Calculate if delivery was on time.
     */
    public function wasOnTime(): ?bool
    {
        if (!$this->actual_datetime || !$this->isCompleted()) {
            return null;
        }

        return $this->actual_datetime->lte($this->scheduled_datetime);
    }

    /**
     * Get time difference between scheduled and actual delivery.
     */
    public function getTimeVarianceInMinutes(): ?int
    {
        if (!$this->actual_datetime) {
            return null;
        }

        return $this->actual_datetime->diffInMinutes($this->scheduled_datetime, false);
    }
}
