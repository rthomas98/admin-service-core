<?php

namespace App\Models;

use App\Enums\TrailerType;
use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Trailer extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'unit_number',
        'vin',
        'year',
        'make',
        'model',
        'type',
        'length',
        'width',
        'height',
        'capacity_weight',
        'capacity_volume',
        'license_plate',
        'registration_state',
        'registration_expiry',
        'status',
        'purchase_date',
        'purchase_price',
        'purchase_vendor',
        'is_leased',
        'lease_end_date',
        'last_inspection_date',
        'next_inspection_date',
        'notes',
        'image_path',
        'specifications',
    ];

    protected $casts = [
        'type' => TrailerType::class,
        'status' => VehicleStatus::class,
        'year' => 'integer',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'capacity_weight' => 'decimal:2',
        'capacity_volume' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'is_leased' => 'boolean',
        'registration_expiry' => 'date',
        'purchase_date' => 'date',
        'lease_end_date' => 'date',
        'last_inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'specifications' => 'array',
    ];

    protected $attributes = [
        'status' => VehicleStatus::Active,
        'type' => TrailerType::Flatbed,
        'is_leased' => false,
        'registration_state' => 'LA',
    ];

    protected $with = [];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', VehicleStatus::Active);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            VehicleStatus::Maintenance,
            VehicleStatus::OutOfService,
        ]);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', VehicleStatus::Active)
            ->whereDoesntHave('activeAssignments');
    }

    public function scopeByType(Builder $query, TrailerType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeLeased(Builder $query): Builder
    {
        return $query->where('is_leased', true);
    }

    public function scopeOwned(Builder $query): Builder
    {
        return $query->where('is_leased', false);
    }

    public function scopeExpiringRegistration(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('registration_expiry', [
            now(),
            now()->addDays($days),
        ]);
    }

    public function scopeExpiringInspection(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('next_inspection_date', [
            now(),
            now()->addDays($days),
        ]);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function finance(): MorphOne
    {
        return $this->morphOne(VehicleFinance::class, 'financeable');
    }

    public function maintenanceLogs(): MorphMany
    {
        return $this->morphMany(MaintenanceLog::class, 'maintainable')
            ->orderBy('service_date', 'desc');
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(TrailerAssignment::class)
            ->whereNull('ended_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TrailerAssignment::class)
            ->orderBy('started_at', 'desc');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(TrailerIncident::class)
            ->orderBy('incident_date', 'desc');
    }

    public function getDisplayNameAttribute(): string
    {
        return sprintf(
            '%s - %s %s',
            $this->unit_number,
            $this->year ? $this->year . ' ' : '',
            trim($this->make . ' ' . ($this->model ?? ''))
        );
    }

    public function getTitleAttribute(): string
    {
        return $this->display_name;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === VehicleStatus::Active 
            && $this->activeAssignments->isEmpty();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === VehicleStatus::Active;
    }

    public function getLastMaintenanceDateAttribute(): ?string
    {
        return $this->maintenanceLogs()
            ->latest('service_date')
            ->value('service_date');
    }

    public function getNextMaintenanceDueAttribute(): ?string
    {
        return $this->maintenanceLogs()
            ->whereNotNull('next_service_due')
            ->latest('service_date')
            ->value('next_service_due');
    }

    public function getDaysUntilRegistrationExpiryAttribute(): ?int
    {
        if (!$this->registration_expiry) {
            return null;
        }

        return now()->diffInDays($this->registration_expiry, false);
    }

    public function getDaysUntilInspectionAttribute(): ?int
    {
        if (!$this->next_inspection_date) {
            return null;
        }

        return now()->diffInDays($this->next_inspection_date, false);
    }

    public function getRegistrationStatusAttribute(): string
    {
        $days = $this->days_until_registration_expiry;

        if ($days === null) {
            return 'unknown';
        }

        if ($days < 0) {
            return 'expired';
        }

        if ($days <= 30) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function getInspectionStatusAttribute(): string
    {
        $days = $this->days_until_inspection;

        if ($days === null) {
            return 'unknown';
        }

        if ($days < 0) {
            return 'overdue';
        }

        if ($days <= 30) {
            return 'due_soon';
        }

        return 'current';
    }

    public function getTotalMaintenanceCostAttribute(): float
    {
        return $this->maintenanceLogs()
            ->sum('cost') ?? 0.00;
    }

    public function getCapacityDisplayAttribute(): string
    {
        $parts = [];
        
        if ($this->capacity_weight) {
            $parts[] = number_format($this->capacity_weight, 0) . ' lbs';
        }
        
        if ($this->capacity_volume) {
            $parts[] = number_format($this->capacity_volume, 0) . ' cu ft';
        }
        
        return implode(' / ', $parts);
    }

    public function getDimensionsDisplayAttribute(): string
    {
        $parts = [];
        
        if ($this->length) {
            $parts[] = $this->length . "'L";
        }
        
        if ($this->width) {
            $parts[] = $this->width . "'W";
        }
        
        if ($this->height) {
            $parts[] = $this->height . "'H";
        }
        
        return implode(' x ', $parts);
    }

    public function isOverdueForMaintenance(): bool
    {
        $nextDue = $this->next_maintenance_due;

        if (!$nextDue) {
            return false;
        }

        return now()->isAfter($nextDue);
    }

    public function isOverdueForInspection(): bool
    {
        if (!$this->next_inspection_date) {
            return false;
        }

        return now()->isAfter($this->next_inspection_date);
    }

    public function assignToDriver(Driver $driver, ?string $notes = null): TrailerAssignment
    {
        if (!$this->is_available) {
            throw new \Exception('Trailer is not available for assignment.');
        }

        return $this->assignments()->create([
            'driver_id' => $driver->id,
            'started_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function returnFromDriver(?string $notes = null): void
    {
        $assignment = $this->activeAssignments()->latest()->first();

        if (!$assignment) {
            throw new \Exception('No active assignment found for this trailer.');
        }

        $assignment->update([
            'ended_at' => now(),
            'notes' => $notes ? $assignment->notes . "\n" . $notes : $assignment->notes,
        ]);
    }

    public function scheduleInspection(\DateTime $date, ?string $notes = null): void
    {
        $this->update([
            'next_inspection_date' => $date,
            'notes' => $notes ? $this->notes . "\n" . $notes : $this->notes,
        ]);
    }

    public static function getValidationRules(): array
    {
        return [
            'unit_number' => ['required', 'string', 'max:50', 'unique:trailers,unit_number'],
            'vin' => ['nullable', 'string', 'size:17', 'unique:trailers,vin'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:' . implode(',', array_column(TrailerType::cases(), 'value'))],
            'status' => ['required', 'string', 'in:' . implode(',', array_column(VehicleStatus::cases(), 'value'))],
            'length' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'width' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:20'],
            'capacity_weight' => ['nullable', 'numeric', 'min:0'],
            'capacity_volume' => ['nullable', 'numeric', 'min:0'],
            'license_plate' => ['nullable', 'string', 'max:20', 'unique:trailers,license_plate'],
            'registration_state' => ['nullable', 'string', 'size:2'],
            'registration_expiry' => ['nullable', 'date', 'after:today'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'is_leased' => ['boolean'],
            'lease_end_date' => ['nullable', 'date', 'after:today', 'required_if:is_leased,true'],
            'last_inspection_date' => ['nullable', 'date', 'before_or_equal:today'],
            'next_inspection_date' => ['nullable', 'date', 'after:today'],
        ];
    }
}
