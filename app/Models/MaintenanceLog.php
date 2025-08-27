<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MaintenanceLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'equipment_id', // Legacy support
        'maintainable_type',
        'maintainable_id',
        'technician_id',
        'driver_id', // For vehicle maintenance
        'service_type',
        'service_date',
        'mileage', // For vehicle maintenance
        'start_time',
        'end_time',
        'service_cost',
        'parts_cost',
        'labor_cost',
        'total_cost',
        'work_performed',
        'parts_used',
        'materials_used',
        'issues_found',
        'recommendations',
        'condition_before',
        'condition_after',
        'checklist_completed',
        'photos',
        'requires_followup',
        'next_service_date',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'service_date' => 'date',
        'next_service_date' => 'date',
        'service_cost' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'parts_used' => 'array',
        'materials_used' => 'array',
        'checklist_completed' => 'array',
        'photos' => 'array',
        'requires_followup' => 'boolean',
        'mileage' => 'integer',
    ];

    /**
     * Get the company that owns the maintenance log.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the maintainable model (equipment, vehicle, or trailer).
     */
    public function maintainable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the equipment for this maintenance log (legacy support).
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the driver who was assigned during maintenance (vehicles only).
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Get the technician who performed the maintenance.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Check if followup is required.
     */
    public function requiresFollowup(): bool
    {
        return $this->requires_followup;
    }

    /**
     * Get the duration of the service in minutes.
     */
    public function getServiceDurationAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }
        
        $start = \Carbon\Carbon::createFromFormat('H:i:s', $this->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i:s', $this->end_time);
        
        return $end->diffInMinutes($start);
    }

    /**
     * Check if equipment condition improved.
     */
    public function conditionImproved(): bool
    {
        if (!$this->condition_before || !$this->condition_after) {
            return false;
        }
        
        $conditions = ['poor', 'needs_repair', 'fair', 'good', 'excellent'];
        $beforeIndex = array_search($this->condition_before, $conditions);
        $afterIndex = array_search($this->condition_after, $conditions);
        
        return $afterIndex > $beforeIndex;
    }
}
