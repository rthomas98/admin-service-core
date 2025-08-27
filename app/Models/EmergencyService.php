<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class EmergencyService extends Model
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
        'emergency_number',
        'request_datetime',
        'urgency_level',
        'emergency_type',
        'description',
        'location_address',
        'location_city',
        'location_parish',
        'location_postal_code',
        'location_latitude',
        'location_longitude',
        'equipment_needed',
        'required_by_datetime',
        'assigned_datetime',
        'dispatched_datetime',
        'arrival_datetime',
        'completion_datetime',
        'target_response_minutes',
        'actual_response_minutes',
        'status',
        'assigned_driver_id',
        'assigned_technician_id',
        'emergency_surcharge',
        'total_cost',
        'completion_notes',
        'photos',
        'contact_phone',
        'contact_name',
        'special_instructions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'request_datetime' => 'datetime',
        'required_by_datetime' => 'datetime',
        'assigned_datetime' => 'datetime',
        'dispatched_datetime' => 'datetime',
        'arrival_datetime' => 'datetime',
        'completion_datetime' => 'datetime',
        'location_latitude' => 'decimal:8',
        'location_longitude' => 'decimal:8',
        'equipment_needed' => 'array',
        'emergency_surcharge' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'photos' => 'array',
    ];

    /**
     * Get the company that owns the emergency service.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the customer for this emergency service.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the assigned driver.
     */
    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    /**
     * Get the assigned technician.
     */
    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    /**
     * Check if emergency is critical.
     */
    public function isCritical(): bool
    {
        return $this->urgency_level === 'critical';
    }

    /**
     * Check if emergency is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if response time target was met.
     */
    public function metResponseTarget(): ?bool
    {
        if (!$this->actual_response_minutes) {
            return null;
        }
        
        return $this->actual_response_minutes <= $this->target_response_minutes;
    }

    /**
     * Get the full location address.
     */
    public function getFullLocationAddressAttribute(): string
    {
        $parts = array_filter([
            $this->location_address,
            $this->location_city,
            $this->location_parish,
            $this->location_postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get response time in hours and minutes.
     */
    public function getFormattedResponseTimeAttribute(): ?string
    {
        if (!$this->actual_response_minutes) {
            return null;
        }
        
        $hours = floor($this->actual_response_minutes / 60);
        $minutes = $this->actual_response_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }

    /**
     * Generate unique emergency number.
     */
    public static function generateEmergencyNumber(): string
    {
        $prefix = 'EMG';
        $date = now()->format('ymd');
        $lastEmergency = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEmergency ? 
            intval(substr($lastEmergency->emergency_number, -3)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
