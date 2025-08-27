<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Pricing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'equipment_type',
        'size',
        'category',
        'daily_rate',
        'weekly_rate',
        'monthly_rate',
        'delivery_fee',
        'pickup_fee',
        'cleaning_fee',
        'maintenance_fee',
        'damage_fee',
        'late_fee_daily',
        'emergency_surcharge',
        'minimum_rental_days',
        'maximum_rental_days',
        'description',
        'included_services',
        'additional_charges',
        'is_active',
        'effective_from',
        'effective_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'pickup_fee' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'maintenance_fee' => 'decimal:2',
        'damage_fee' => 'decimal:2',
        'late_fee_daily' => 'decimal:2',
        'emergency_surcharge' => 'decimal:2',
        'included_services' => 'array',
        'additional_charges' => 'array',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    /**
     * Get the company that owns the pricing.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if pricing is currently active and within effective dates.
     */
    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = Carbon::today();

        // Check if we're within the effective date range
        if ($this->effective_from && $today->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $today->gt($this->effective_until)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate rental cost based on number of days.
     */
    public function calculateRentalCost(int $days): float
    {
        if ($days >= 30 && $this->monthly_rate) {
            $months = floor($days / 30);
            $remainingDays = $days % 30;
            
            $cost = $months * $this->monthly_rate;
            
            if ($remainingDays > 0) {
                if ($remainingDays >= 7 && $this->weekly_rate) {
                    $weeks = floor($remainingDays / 7);
                    $finalDays = $remainingDays % 7;
                    
                    $cost += $weeks * $this->weekly_rate;
                    $cost += $finalDays * ($this->daily_rate ?? 0);
                } else {
                    $cost += $remainingDays * ($this->daily_rate ?? 0);
                }
            }
            
            return $cost;
        }

        if ($days >= 7 && $this->weekly_rate) {
            $weeks = floor($days / 7);
            $remainingDays = $days % 7;
            
            $cost = $weeks * $this->weekly_rate;
            $cost += $remainingDays * ($this->daily_rate ?? 0);
            
            return $cost;
        }

        return $days * ($this->daily_rate ?? 0);
    }

    /**
     * Get the best rate description for display.
     */
    public function getBestRateDescriptionAttribute(): string
    {
        $rates = [];
        
        if ($this->daily_rate) {
            $rates[] = '$' . number_format($this->daily_rate, 2) . '/day';
        }
        
        if ($this->weekly_rate) {
            $rates[] = '$' . number_format($this->weekly_rate, 2) . '/week';
        }
        
        if ($this->monthly_rate) {
            $rates[] = '$' . number_format($this->monthly_rate, 2) . '/month';
        }

        return implode(' • ', $rates);
    }

    /**
     * Get total additional fees.
     */
    public function getTotalAdditionalFeesAttribute(): float
    {
        return ($this->delivery_fee ?? 0) + 
               ($this->pickup_fee ?? 0) + 
               ($this->cleaning_fee ?? 0);
    }
}
