<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'driver_id',
        'inspection_number',
        'inspection_date',
        'inspection_time',
        'inspection_type',
        'status',
        'odometer_reading',
        'exterior_items',
        'interior_items',
        'engine_items',
        'safety_items',
        'documentation_items',
        'issues_found',
        'notes',
        'corrective_actions',
        'inspector_name',
        'inspector_signature',
        'inspector_certification_number',
        'certified_at',
        'next_inspection_date',
        'next_inspection_miles',
        'photos',
        'documents',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'inspection_time' => 'datetime:H:i',
        'certified_at' => 'datetime',
        'next_inspection_date' => 'date',
        'exterior_items' => 'array',
        'interior_items' => 'array',
        'engine_items' => 'array',
        'safety_items' => 'array',
        'documentation_items' => 'array',
        'issues_found' => 'array',
        'photos' => 'array',
        'documents' => 'array',
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

    public function isPassed(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'needs_repair']);
    }

    public function isOverdue(): bool
    {
        if (!$this->next_inspection_date) {
            return false;
        }
        return $this->next_inspection_date->isPast();
    }

    public function getDaysUntilNextInspectionAttribute(): ?int
    {
        if (!$this->next_inspection_date) {
            return null;
        }
        return now()->diffInDays($this->next_inspection_date, false);
    }

    public static function generateInspectionNumber(): string
    {
        $prefix = 'INSP';
        $date = now()->format('ymd');
        $lastInspection = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInspection ? 
            intval(substr($lastInspection->inspection_number, -3)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}