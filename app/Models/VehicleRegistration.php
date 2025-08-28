<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class VehicleRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'registration_number',
        'license_plate',
        'registration_state',
        'registration_date',
        'expiry_date',
        'status',
        'renewal_reminder_date',
        'auto_renew',
        'last_renewal_date',
        'next_renewal_date',
        'renewal_notice_days',
        'registration_fee',
        'renewal_fee',
        'late_fee',
        'other_fees',
        'total_paid',
        'payment_status',
        'payment_date',
        'payment_method',
        'transaction_number',
        'vin',
        'make',
        'model',
        'year',
        'color',
        'weight',
        'vehicle_class',
        'fuel_type',
        'registered_owner',
        'owner_address',
        'owner_city',
        'owner_state',
        'owner_zip',
        'insurance_company',
        'insurance_policy_number',
        'insurance_expiry_date',
        'permits',
        'endorsements',
        'dot_compliant',
        'dot_number',
        'mc_number',
        'registration_document',
        'insurance_document',
        'other_documents',
        'photos',
        'notes',
        'renewal_history',
        'violation_history',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'expiry_date' => 'date',
        'renewal_reminder_date' => 'date',
        'last_renewal_date' => 'date',
        'next_renewal_date' => 'date',
        'payment_date' => 'date',
        'insurance_expiry_date' => 'date',
        'auto_renew' => 'boolean',
        'dot_compliant' => 'boolean',
        'permits' => 'array',
        'endorsements' => 'array',
        'other_documents' => 'array',
        'photos' => 'array',
        'renewal_history' => 'array',
        'violation_history' => 'array',
        'registration_fee' => 'decimal:2',
        'renewal_fee' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->expiry_date->isPast();
    }

    public function isExpiring(): bool
    {
        if ($this->isExpired()) {
            return false;
        }
        
        $daysUntilExpiry = $this->getDaysUntilExpiryAttribute();
        return $daysUntilExpiry !== null && $daysUntilExpiry <= $this->renewal_notice_days;
    }

    public function needsRenewal(): bool
    {
        return in_array($this->status, ['expired', 'pending_renewal']) || $this->isExpiring();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->isExpired()) {
            return null;
        }
        return abs($this->getDaysUntilExpiryAttribute());
    }

    public function calculateTotalFees(): float
    {
        $total = $this->registration_fee + $this->renewal_fee + $this->other_fees;
        
        if ($this->isExpired()) {
            $total += $this->late_fee;
        }
        
        return $total;
    }

    public function getFullOwnerAddressAttribute(): string
    {
        $parts = array_filter([
            $this->owner_address,
            $this->owner_city,
            $this->owner_state,
            $this->owner_zip,
        ]);

        return implode(', ', $parts);
    }

    public function addRenewalHistory(array $data): void
    {
        $history = $this->renewal_history ?? [];
        $history[] = array_merge($data, [
            'renewed_at' => now()->toDateTimeString(),
        ]);
        $this->renewal_history = $history;
    }

    public function addViolation(array $data): void
    {
        $violations = $this->violation_history ?? [];
        $violations[] = array_merge($data, [
            'recorded_at' => now()->toDateTimeString(),
        ]);
        $this->violation_history = $violations;
    }

    public static function generateRegistrationNumber(): string
    {
        $prefix = 'REG';
        $date = now()->format('ymd');
        $lastRegistration = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastRegistration ? 
            intval(substr($lastRegistration->registration_number, -4)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-calculate renewal reminder date if not set
            if (!$model->renewal_reminder_date && $model->expiry_date) {
                $model->renewal_reminder_date = Carbon::parse($model->expiry_date)
                    ->subDays($model->renewal_notice_days ?? 30);
            }
            
            // Auto-update status based on dates
            if ($model->expiry_date && $model->expiry_date->isPast()) {
                $model->status = 'expired';
            } elseif ($model->isExpiring()) {
                $model->status = 'pending_renewal';
            }
        });
    }
}