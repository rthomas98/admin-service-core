<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'type',
        'size',
        'unit_number',
        'serial_number',
        'manufacturer',
        'model',
        'year',
        'weight_capacity',
        'dimensions',
        'status',
        'condition',
        'color',
        'current_location',
        'latitude',
        'longitude',
        'purchase_date',
        'last_service_date',
        'next_service_due',
        'service_interval',
        'service_provider',
        'service_contact',
        'purchase_price',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'delivery_fee',
        'pickup_fee',
        'cleaning_fee',
        'damage_deposit',
        'requires_cdl',
        'has_gps_tracker',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'last_service_date' => 'date',
        'next_service_due' => 'date',
        'purchase_price' => 'decimal:2',
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'pickup_fee' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'damage_deposit' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'requires_cdl' => 'boolean',
        'has_gps_tracker' => 'boolean',
        'year' => 'integer',
    ];

    /**
     * Get the company that owns the equipment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the service orders that use this equipment.
     */
    public function serviceOrders(): BelongsToMany
    {
        return $this->belongsToMany(ServiceOrder::class, 'service_order_equipment')
            ->withTimestamps();
    }

    /**
     * Get the maintenance logs for this equipment.
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    /**
     * Get the delivery schedules for this equipment.
     */
    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    /**
     * Get the service schedules for this equipment.
     */
    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class);
    }

    /**
     * Check if equipment is available for rental.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if equipment is currently rented.
     */
    public function isRented(): bool
    {
        return $this->status === 'rented';
    }

    /**
     * Check if equipment is due for maintenance.
     */
    public function isDueForMaintenance(): bool
    {
        return $this->next_service_due &&
               $this->next_service_due->lte(Carbon::today());
    }

    /**
     * Get the days since last service.
     */
    public function daysSinceLastService(): ?int
    {
        if (! $this->last_service_date) {
            return null;
        }

        return $this->last_service_date->diffInDays(Carbon::today());
    }

    /**
     * Get equipment display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->unit_number} - ".ucfirst(str_replace('_', ' ', $this->type)).
               ($this->size ? " ({$this->size})" : '');
    }

    /**
     * Get the work orders for this equipment.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }

    /**
     * Get the current work order if equipment is rented.
     */
    public function currentWorkOrder()
    {
        return $this->workOrders()
            ->whereIn('status', ['in_progress', 'assigned'])
            ->latest()
            ->first();
    }

    /**
     * Calculate rental price based on duration.
     */
    public function calculateRentalPrice(int $days): float
    {
        if ($days >= 30 && $this->monthly_rate) {
            $months = floor($days / 30);
            $remainingDays = $days % 30;

            return ($months * $this->monthly_rate) + ($remainingDays * $this->daily_rate);
        } elseif ($days >= 7 && $this->weekly_rate) {
            $weeks = floor($days / 7);
            $remainingDays = $days % 7;

            return ($weeks * $this->weekly_rate) + ($remainingDays * $this->daily_rate);
        } else {
            return $days * ($this->daily_rate ?? 0);
        }
    }

    /**
     * Get equipment category based on type.
     */
    public function getCategoryAttribute(): string
    {
        if (! $this->type) {
            return 'Other';
        }

        try {
            $enumType = \App\Enums\EquipmentType::from($this->type);

            return $enumType->category();
        } catch (\ValueError $e) {
            return 'Other';
        }
    }

    /**
     * Get equipment icon based on type.
     */
    public function getIconAttribute(): string
    {
        if (! $this->type) {
            return 'heroicon-o-question-mark-circle';
        }

        try {
            $enumType = \App\Enums\EquipmentType::from($this->type);

            return $enumType->icon();
        } catch (\ValueError $e) {
            return 'heroicon-o-question-mark-circle';
        }
    }

    /**
     * Check if equipment needs service based on interval.
     */
    public function needsService(): bool
    {
        if (! $this->service_interval || ! $this->last_service_date) {
            return false;
        }

        $daysSinceService = $this->daysSinceLastService();

        return match ($this->service_interval) {
            'weekly' => $daysSinceService >= 7,
            'biweekly' => $daysSinceService >= 14,
            'monthly' => $daysSinceService >= 30,
            'quarterly' => $daysSinceService >= 90,
            'annually' => $daysSinceService >= 365,
            default => false,
        };
    }

    /**
     * Mark equipment as rented.
     */
    public function markAsRented(?int $workOrderId = null): void
    {
        $this->update([
            'status' => 'rented',
        ]);

        if ($workOrderId) {
            $this->workOrders()->attach($workOrderId);
        }
    }

    /**
     * Mark equipment as available.
     */
    public function markAsAvailable(): void
    {
        $this->update([
            'status' => 'available',
            'current_location' => 'Warehouse',
        ]);
    }

    /**
     * Scope for available equipment.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for equipment by category.
     */
    public function scopeByCategory($query, string $category)
    {
        $types = \App\Enums\EquipmentType::forCategory($category);
        $values = array_map(fn ($type) => $type->value, $types);

        return $query->whereIn('type', $values);
    }

    /**
     * Scope for equipment needing service.
     */
    public function scopeNeedsService($query)
    {
        return $query->where(function ($q) {
            $q->whereDate('next_service_due', '<=', Carbon::today())
                ->orWhereNull('last_service_date');
        });
    }
}
