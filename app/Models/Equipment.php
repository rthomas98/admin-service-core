<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

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
        'status',
        'condition',
        'current_location',
        'latitude',
        'longitude',
        'purchase_date',
        'last_service_date',
        'next_service_due',
        'purchase_price',
        'color',
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
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
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
        if (!$this->last_service_date) {
            return null;
        }
        
        return $this->last_service_date->diffInDays(Carbon::today());
    }

    /**
     * Get equipment display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->unit_number} - " . ucfirst(str_replace('_', ' ', $this->type)) . 
               ($this->size ? " ({$this->size})" : '');
    }
}
