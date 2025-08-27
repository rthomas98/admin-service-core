<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'email',
        'phone',
        'address',
        'website',
        'logo',
        'primary_color',
        'settings',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
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
}