<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'equipment_id',
        'technician_id',
        'service_type',
        'scheduled_datetime',
        'completed_datetime',
        'status',
        'priority',
        'service_description',
        'completion_notes',
        'checklist_items',
        'materials_used',
        'service_cost',
        'materials_cost',
        'total_cost',
        'estimated_duration_minutes',
        'actual_duration_minutes',
        'requires_followup',
        'followup_date',
        'photos',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_datetime' => 'datetime',
        'completed_datetime' => 'datetime',
        'checklist_items' => 'array',
        'materials_used' => 'array',
        'service_cost' => 'decimal:2',
        'materials_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'requires_followup' => 'boolean',
        'followup_date' => 'date',
        'photos' => 'array',
    ];

    /**
     * Get the company that owns the service schedule.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the equipment for this service schedule.
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Get the technician assigned to this service.
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Check if service is scheduled for today.
     */
    public function isToday(): bool
    {
        return $this->scheduled_datetime->isToday();
    }

    /**
     * Check if service is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->scheduled_datetime->isPast() && 
               !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if service is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if service is urgent priority.
     */
    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
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
     * Calculate if service was completed on time.
     */
    public function wasOnTime(): ?bool
    {
        if (!$this->completed_datetime || !$this->isCompleted()) {
            return null;
        }

        return $this->completed_datetime->lte($this->scheduled_datetime);
    }

    /**
     * Get completion rate of checklist items.
     */
    public function getChecklistCompletionRateAttribute(): float
    {
        if (!$this->checklist_items || empty($this->checklist_items)) {
            return 0.0;
        }

        $completed = 0;
        foreach ($this->checklist_items as $item) {
            if (isset($item['completed']) && $item['completed']) {
                $completed++;
            }
        }

        return (float) ($completed / count($this->checklist_items)) * 100;
    }
}
