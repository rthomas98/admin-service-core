<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'account_number',
        'contact_name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'zip',
        'website',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicleFinances(): HasMany
    {
        return $this->hasMany(VehicleFinance::class);
    }

    public function activeFinances(): HasMany
    {
        return $this->hasMany(VehicleFinance::class)
            ->where('is_active', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    public function getTitleAttribute(): string
    {
        return $this->display_name;
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state . ($this->zip ? ' ' . $this->zip : ''),
        ]);

        return implode(', ', $parts) ?: null;
    }

    public function getTotalFinancedAmountAttribute(): float
    {
        return $this->activeFinances()
            ->sum('total_amount') ?? 0.00;
    }

    public function getTotalMonthlyPaymentsAttribute(): float
    {
        return $this->activeFinances()
            ->sum('monthly_payment') ?? 0.00;
    }

    public function getActiveFinanceCountAttribute(): int
    {
        return $this->activeFinances()->count();
    }

    public static function getValidationRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'zip' => ['nullable', 'string', 'max:10'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}