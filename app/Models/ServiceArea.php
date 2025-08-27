<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceArea extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'zip_codes',
        'parishes',
        'boundaries',
        'delivery_surcharge',
        'pickup_surcharge',
        'emergency_surcharge',
        'standard_delivery_days',
        'rush_delivery_hours',
        'rush_delivery_surcharge',
        'is_active',
        'priority',
        'service_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'zip_codes' => 'array',
        'parishes' => 'array',
        'boundaries' => 'array',
        'delivery_surcharge' => 'decimal:2',
        'pickup_surcharge' => 'decimal:2',
        'emergency_surcharge' => 'decimal:2',
        'rush_delivery_surcharge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the service area.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if service area is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if service area covers a specific ZIP code.
     */
    public function coversZipCode(string $zipCode): bool
    {
        return $this->zip_codes && in_array($zipCode, $this->zip_codes);
    }

    /**
     * Check if service area covers a specific parish.
     */
    public function coversParish(string $parish): bool
    {
        return $this->parishes && in_array($parish, $this->parishes);
    }

    /**
     * Get total delivery cost including surcharges.
     */
    public function getTotalDeliveryCost(bool $isRush = false, bool $isEmergency = false): float
    {
        $cost = (float) $this->delivery_surcharge;
        
        if ($isRush) {
            $cost += (float) $this->rush_delivery_surcharge;
        }
        
        if ($isEmergency) {
            $cost += (float) $this->emergency_surcharge;
        }
        
        return $cost;
    }
}
