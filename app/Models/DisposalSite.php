<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisposalSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'location',
        'parish',
        'site_type',
        'total_capacity',
        'current_capacity',
        'daily_intake_average',
        'status',
        'manager_name',
        'contact_phone',
        'operating_hours',
        'environmental_permit',
        'last_inspection_date',
    ];

    protected $casts = [
        'total_capacity' => 'decimal:2',
        'current_capacity' => 'decimal:2',
        'daily_intake_average' => 'decimal:2',
        'last_inspection_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}