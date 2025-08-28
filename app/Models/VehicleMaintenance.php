<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class VehicleMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'driver_id',
        'maintenance_number',
        'maintenance_type',
        'priority',
        'status',
        'scheduled_date',
        'scheduled_time',
        'completed_date',
        'completed_time',
        'odometer_at_service',
        'next_service_miles',
        'next_service_date',
        'description',
        'work_performed',
        'parts_replaced',
        'fluids_added',
        'service_provider',
        'technician_name',
        'work_order_number',
        'labor_cost',
        'parts_cost',
        'other_cost',
        'total_cost',
        'invoice_number',
        'payment_status',
        'under_warranty',
        'warranty_claim_number',
        'warranty_covered_amount',
        'vehicle_down_from',
        'vehicle_down_to',
        'total_downtime_hours',
        'notes',
        'recommendations',
        'photos',
        'documents',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
        'completed_date' => 'date',
        'completed_time' => 'datetime:H:i',
        'next_service_date' => 'date',
        'vehicle_down_from' => 'datetime',
        'vehicle_down_to' => 'datetime',
        'work_performed' => 'array',
        'parts_replaced' => 'array',
        'fluids_added' => 'array',
        'photos' => 'array',
        'documents' => 'array',
        'labor_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'other_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'warranty_covered_amount' => 'decimal:2',
        'under_warranty' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }
        return $this->scheduled_date->isPast();
    }

    public function isDue(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }
        $daysUntil = $this->getDaysUntilDueAttribute();
        return $daysUntil !== null && $daysUntil <= 7 && $daysUntil >= 0;
    }

    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->scheduled_date || $this->status === 'completed') {
            return null;
        }
        return now()->diffInDays($this->scheduled_date, false);
    }

    public function calculateTotalCost(): float
    {
        return $this->labor_cost + $this->parts_cost + $this->other_cost;
    }

    public function calculateDowntimeHours(): ?int
    {
        if (!$this->vehicle_down_from || !$this->vehicle_down_to) {
            return null;
        }
        return $this->vehicle_down_from->diffInHours($this->vehicle_down_to);
    }

    public static function generateMaintenanceNumber(): string
    {
        $prefix = 'MAINT';
        $date = now()->format('ymd');
        $lastMaintenance = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastMaintenance ? 
            intval(substr($lastMaintenance->maintenance_number, -3)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-calculate total cost
            $model->total_cost = $model->calculateTotalCost();
            
            // Auto-calculate downtime hours
            if ($model->vehicle_down_from && $model->vehicle_down_to) {
                $model->total_downtime_hours = $model->calculateDowntimeHours();
            }
        });
    }
}