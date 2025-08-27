<?php

namespace App\Models;

use App\Enums\FuelType;
use App\Enums\VehicleStatus;
use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Vehicle extends Model
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
        'color',
        'license_plate',
        'registration_state',
        'registration_expiry',
        'odometer',
        'odometer_date',
        'fuel_capacity',
        'fuel_type',
        'status',
        'purchase_date',
        'purchase_price',
        'purchase_vendor',
        'is_leased',
        'lease_end_date',
        'notes',
        'image_path',
        'specifications',
    ];

    protected $casts = [
        'type' => VehicleType::class,
        'fuel_type' => FuelType::class,
        'status' => VehicleStatus::class,
        'year' => 'integer',
        'odometer' => 'integer',
        'fuel_capacity' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'is_leased' => 'boolean',
        'registration_expiry' => 'date',
        'odometer_date' => 'date',
        'purchase_date' => 'date',
        'lease_end_date' => 'date',
        'specifications' => 'array',
    ];

    protected $attributes = [
        'status' => VehicleStatus::Active,
        'type' => VehicleType::Truck,
        'fuel_type' => FuelType::Diesel,
        'is_leased' => false,
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

    public function scopeByType(Builder $query, VehicleType $type): Builder
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

    public function scopeHighMileage(Builder $query, int $threshold = 100000): Builder
    {
        return $query->where('odometer', '>=', $threshold);
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
        return $this->hasMany(VehicleAssignment::class)
            ->whereNull('ended_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class)
            ->orderBy('started_at', 'desc');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class)
            ->orderBy('fill_date', 'desc');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(VehicleIncident::class)
            ->orderBy('incident_date', 'desc');
    }

    public function getDisplayNameAttribute(): string
    {
        return sprintf(
            '%s - %d %s %s',
            $this->unit_number,
            $this->year,
            $this->make,
            $this->model
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

    public function getMilesSinceLastServiceAttribute(): ?int
    {
        $lastService = $this->maintenanceLogs()
            ->whereNotNull('odometer_reading')
            ->latest('service_date')
            ->value('odometer_reading');

        if (!$lastService || !$this->odometer) {
            return null;
        }

        return $this->odometer - $lastService;
    }

    public function getTotalMaintenanceCostAttribute(): float
    {
        return $this->maintenanceLogs()
            ->sum('cost') ?? 0.00;
    }

    public function getAverageFuelEconomyAttribute(): ?float
    {
        $economy = $this->fuelLogs()
            ->whereNotNull('miles_per_gallon')
            ->avg('miles_per_gallon');

        return $economy ? round($economy, 2) : null;
    }

    public function isOverdueForMaintenance(): bool
    {
        $nextDue = $this->next_maintenance_due;

        if (!$nextDue) {
            return false;
        }

        return now()->isAfter($nextDue);
    }

    public function needsOilChange(int $mileageThreshold = 5000): bool
    {
        $lastOilChange = $this->maintenanceLogs()
            ->where('type', 'oil_change')
            ->latest('service_date')
            ->first();

        if (!$lastOilChange || !$lastOilChange->odometer_reading) {
            return true;
        }

        return ($this->odometer - $lastOilChange->odometer_reading) >= $mileageThreshold;
    }

    public function assignToDriver(Driver $driver, ?string $notes = null): VehicleAssignment
    {
        if (!$this->is_available) {
            throw new \Exception('Vehicle is not available for assignment.');
        }

        return $this->assignments()->create([
            'driver_id' => $driver->id,
            'started_at' => now(),
            'starting_odometer' => $this->odometer,
            'notes' => $notes,
        ]);
    }

    public function returnFromDriver(?int $endingOdometer = null, ?string $notes = null): void
    {
        $assignment = $this->activeAssignments()->latest()->first();

        if (!$assignment) {
            throw new \Exception('No active assignment found for this vehicle.');
        }

        $assignment->update([
            'ended_at' => now(),
            'ending_odometer' => $endingOdometer ?? $this->odometer,
            'notes' => $notes ? $assignment->notes . "\n" . $notes : $assignment->notes,
        ]);

        if ($endingOdometer) {
            $this->update([
                'odometer' => $endingOdometer,
                'odometer_date' => now(),
            ]);
        }
    }

    public function updateOdometer(int $reading): void
    {
        if ($reading < $this->odometer) {
            throw new \Exception('New odometer reading cannot be less than current reading.');
        }

        $this->update([
            'odometer' => $reading,
            'odometer_date' => now(),
        ]);
    }

    public static function getValidationRules(): array
    {
        return [
            'unit_number' => ['required', 'string', 'max:50', 'unique:vehicles,unit_number'],
            'vin' => ['nullable', 'string', 'size:17', 'unique:vehicles,vin'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'type' => ['required', 'string', 'in:' . implode(',', array_column(VehicleType::cases(), 'value'))],
            'fuel_type' => ['required', 'string', 'in:' . implode(',', array_column(FuelType::cases(), 'value'))],
            'status' => ['required', 'string', 'in:' . implode(',', array_column(VehicleStatus::cases(), 'value'))],
            'license_plate' => ['nullable', 'string', 'max:20', 'unique:vehicles,license_plate'],
            'registration_state' => ['nullable', 'string', 'size:2'],
            'registration_expiry' => ['nullable', 'date', 'after:today'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'fuel_capacity' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'is_leased' => ['boolean'],
            'lease_end_date' => ['nullable', 'date', 'after:today', 'required_if:is_leased,true'],
        ];
    }
}