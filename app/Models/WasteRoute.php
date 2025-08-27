<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WasteRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'zone',
        'frequency',
        'estimated_duration_hours',
        'total_distance_km',
        'status',
    ];

    protected $casts = [
        'estimated_duration_hours' => 'decimal:2',
        'total_distance_km' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function collections(): HasMany
    {
        return $this->hasMany(WasteCollection::class, 'route_id');
    }
}