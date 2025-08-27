<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WasteCollection extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_id',
        'route_id',
        'driver_id',
        'truck_id',
        'scheduled_date',
        'scheduled_time',
        'waste_type',
        'estimated_weight',
        'actual_weight',
        'status',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'scheduled_time' => 'datetime:H:i',
        'completed_at' => 'datetime',
        'estimated_weight' => 'decimal:2',
        'actual_weight' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(WasteRoute::class, 'route_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function truck(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'truck_id');
    }
}