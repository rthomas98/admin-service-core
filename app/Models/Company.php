<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'service_provider_id',
        'company_type',
        'email',
        'billing_email',
        'phone',
        'address',
        'billing_address',
        'city',
        'state',
        'postal_code',
        'country',
        'website',
        'logo',
        'primary_color',
        'settings',
        'is_active',
        'onboarding_completed',
        'onboarded_at',
        'onboarding_steps',
        'tax_id',
        'business_type',
        'industry',
        'description',
        'contact_name',
        'contact_title',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'onboarding_completed' => 'boolean',
        'onboarded_at' => 'datetime',
        'onboarding_steps' => 'array',
    ];

    /**
     * Get the users that belong to the company.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the company admins.
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('role', 'admin')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the company managers.
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->wherePivotIn('role', ['admin', 'manager'])
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Check if this is LIV Transport company.
     */
    public function isLivTransport(): bool
    {
        return $this->slug === 'liv-transport';
    }

    /**
     * Check if this is RAW Disposal company.
     */
    public function isRawDisposal(): bool
    {
        return $this->slug === 'raw-disposal';
    }

    /**
     * Get the service provider company this company is a customer of.
     */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'service_provider_id');
    }

    /**
     * Get the customer companies served by this service provider.
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Company::class, 'service_provider_id');
    }

    /**
     * Check if this is a service provider company.
     */
    public function isServiceProvider(): bool
    {
        return $this->company_type === 'service_provider';
    }

    /**
     * Check if this is a customer company.
     */
    public function isCustomer(): bool
    {
        return $this->company_type === 'customer';
    }
}
