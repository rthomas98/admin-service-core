<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'driver_id',
        'vehicle_id',
        'trailer_id',
        'start_date',
        'end_date',
        'status',
        'route',
        'origin',
        'destination',
        'cargo_type',
        'cargo_weight',
        'expected_duration_hours',
        'actual_duration_hours',
        'mileage_start',
        'mileage_end',
        'fuel_used',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'cargo_weight' => 'decimal:2',
        'expected_duration_hours' => 'decimal:2',
        'actual_duration_hours' => 'decimal:2',
        'mileage_start' => 'integer',
        'mileage_end' => 'integer',
        'fuel_used' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'scheduled',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Trailer::class);
    }

    public function getTotalMileageAttribute(): ?int
    {
        if ($this->mileage_start && $this->mileage_end) {
            return $this->mileage_end - $this->mileage_start;
        }

        return null;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' 
            && $this->start_date->isPast() 
            && (!$this->end_date || $this->end_date->isFuture());
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed' || ($this->end_date && $this->end_date->isPast());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
            ->where('start_date', '>', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}