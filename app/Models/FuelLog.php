<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'vehicle_id',
        'driver_id',
        'driver_assignment_id',
        'fuel_date',
        'fuel_station',
        'location',
        'gallons',
        'price_per_gallon',
        'total_cost',
        'odometer_reading',
        'fuel_type',
        'payment_method',
        'receipt_number',
        'receipt_image',
        'is_personal',
        'notes',
    ];

    protected $casts = [
        'fuel_date' => 'datetime',
        'gallons' => 'decimal:2',
        'price_per_gallon' => 'decimal:3',
        'total_cost' => 'decimal:2',
        'odometer_reading' => 'integer',
        'is_personal' => 'boolean',
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

    public function driverAssignment(): BelongsTo
    {
        return $this->belongsTo(DriverAssignment::class);
    }

    public function getMpgAttribute(): ?float
    {
        if (!$this->vehicle_id || !$this->odometer_reading || !$this->gallons) {
            return null;
        }

        // Get the previous fuel log for this vehicle
        $previousLog = self::where('vehicle_id', $this->vehicle_id)
            ->where('fuel_date', '<', $this->fuel_date)
            ->orderBy('fuel_date', 'desc')
            ->first();

        if (!$previousLog || !$previousLog->odometer_reading) {
            return null;
        }

        $milesDriven = $this->odometer_reading - $previousLog->odometer_reading;
        
        if ($milesDriven <= 0) {
            return null;
        }

        return round($milesDriven / $this->gallons, 2);
    }

    public function getCostPerMileAttribute(): ?float
    {
        if (!$this->vehicle_id || !$this->odometer_reading || !$this->total_cost) {
            return null;
        }

        // Get the previous fuel log for this vehicle
        $previousLog = self::where('vehicle_id', $this->vehicle_id)
            ->where('fuel_date', '<', $this->fuel_date)
            ->orderBy('fuel_date', 'desc')
            ->first();

        if (!$previousLog || !$previousLog->odometer_reading) {
            return null;
        }

        $milesDriven = $this->odometer_reading - $previousLog->odometer_reading;
        
        if ($milesDriven <= 0) {
            return null;
        }

        return round($this->total_cost / $milesDriven, 4);
    }

    public function scopePersonal($query)
    {
        return $query->where('is_personal', true);
    }

    public function scopeBusiness($query)
    {
        return $query->where('is_personal', false);
    }

    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('fuel_date', [$startDate, $endDate]);
    }
}