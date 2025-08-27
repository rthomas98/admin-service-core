<?php

namespace App\Models;

use App\Enums\DriverStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Driver extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'license_number',
        'license_class',
        'license_expiry_date',
        'vehicle_type',
        'vehicle_registration',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'service_areas',
        'can_lift_heavy',
        'has_truck_crane',
        'hourly_rate',
        'shift_start_time',
        'shift_end_time',
        'available_days',
        'status',
        'notes',
        'hired_date',
        // LIV Transport specific fields
        'employee_id',
        'emergency_contact',
        'emergency_phone',
        'date_of_birth',
        'license_state',
        'medical_card_expiry',
        'hazmat_expiry',
        'twic_card_expiry',
        'address',
        'city',
        'state',
        'zip',
        'employment_type',
        'drug_test_passed',
        'last_drug_test_date',
        'next_drug_test_date',
        'total_miles_driven',
        'safety_score',
        'photo',
        'documents',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'license_expiry_date' => 'date',
        'service_areas' => 'array',
        'can_lift_heavy' => 'boolean',
        'has_truck_crane' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'available_days' => 'array',
        'hired_date' => 'date',
        'shift_start_time' => 'datetime:H:i:s',
        'shift_end_time' => 'datetime:H:i:s',
        'status' => DriverStatus::class,
        // LIV Transport specific casts
        'date_of_birth' => 'date',
        'medical_card_expiry' => 'date',
        'hazmat_expiry' => 'date',
        'twic_card_expiry' => 'date',
        'drug_test_passed' => 'boolean',
        'last_drug_test_date' => 'date',
        'next_drug_test_date' => 'date',
        'total_miles_driven' => 'integer',
        'safety_score' => 'integer',
        'documents' => 'array',
    ];

    protected $with = [];

    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && Auth::user()->current_company_id) {
                $builder->where('drivers.company_id', Auth::user()->current_company_id);
            }
        });
    }

    /**
     * Get the company that owns the driver.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user account associated with the driver.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the delivery schedules assigned to this driver.
     */
    public function deliverySchedules(): HasMany
    {
        return $this->hasMany(DeliverySchedule::class);
    }

    /**
     * Get the service schedules assigned to this driver.
     */
    public function serviceSchedules(): HasMany
    {
        return $this->hasMany(ServiceSchedule::class, 'technician_id', 'user_id');
    }

    /**
     * Get the driver assignments for this driver.
     */
    public function driverAssignments(): HasMany
    {
        return $this->hasMany(DriverAssignment::class);
    }

    /**
     * Check if driver is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === DriverStatus::ACTIVE;
    }

    /**
     * Check if driver's license is expired or expiring soon.
     */
    public function isLicenseExpiring(int $daysAhead = 30): bool
    {
        return $this->license_expiry_date->lte(Carbon::today()->addDays($daysAhead));
    }

    /**
     * Check if driver is available on a specific day.
     */
    public function isAvailableOnDay(string $dayOfWeek): bool
    {
        return $this->available_days && in_array($dayOfWeek, $this->available_days);
    }

    /**
     * Check if driver can service a specific area.
     */
    public function canServiceArea(string $area): bool
    {
        return $this->service_areas && in_array($area, $this->service_areas);
    }

    /**
     * Get driver's full name.
     */
    public function getFullNameAttribute(): string
    {
        $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        
        // If both names are empty, return a fallback identifier
        if (empty($name)) {
            if ($this->email) {
                return $this->email;
            }
            if ($this->employee_id) {
                return "Driver #{$this->employee_id}";
            }
            return "Driver #{$this->id}";
        }
        
        return $name;
    }

    /**
     * Get driver's name (alias for full name).
     */
    public function getNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get vehicle description.
     */
    public function getVehicleDescriptionAttribute(): ?string
    {
        if (!$this->vehicle_make || !$this->vehicle_model) {
            return $this->vehicle_type;
        }

        $parts = array_filter([
            $this->vehicle_year,
            $this->vehicle_make,
            $this->vehicle_model,
            $this->vehicle_type ? "({$this->vehicle_type})" : null,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get today's delivery schedules for this driver.
     */
    public function getTodayDeliveries()
    {
        return $this->deliverySchedules()
            ->whereDate('scheduled_datetime', Carbon::today())
            ->orderBy('scheduled_datetime')
            ->get();
    }

    /**
     * Check if driver is available for a specific time slot.
     */
    public function isAvailableForTimeSlot(Carbon $dateTime): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        // Check if day of week is available
        if (!$this->isAvailableOnDay($dateTime->format('l'))) {
            return false;
        }

        // Check shift times if set
        if ($this->shift_start_time && $this->shift_end_time) {
            $time = $dateTime->format('H:i:s');
            return $time >= $this->shift_start_time && $time <= $this->shift_end_time;
        }

        return true;
    }

    // Query Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeCanLiftHeavy(Builder $query): Builder
    {
        return $query->where('can_lift_heavy', true);
    }

    public function scopeHasTruckCrane(Builder $query): Builder
    {
        return $query->where('has_truck_crane', true);
    }

    public function scopeAvailableOn(Builder $query, string $dayOfWeek): Builder
    {
        return $query->whereJsonContains('available_days', $dayOfWeek);
    }

    public function scopeServicesArea(Builder $query, string $area): Builder
    {
        return $query->whereJsonContains('service_areas', $area);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('license_number', 'like', "%{$search}%")
              ->orWhere('vehicle_registration', 'like', "%{$search}%");
        });
    }

    // Validation Rules
    public static function validationRules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:drivers,email',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'required|string|unique:drivers,license_number',
            'license_class' => 'required|string|max:50',
            'license_expiry_date' => 'required|date|after:today',
            'status' => 'required|in:active,inactive,suspended',
            'hourly_rate' => 'nullable|numeric|min:0',
            'can_lift_heavy' => 'boolean',
            'has_truck_crane' => 'boolean',
        ];
    }
}
