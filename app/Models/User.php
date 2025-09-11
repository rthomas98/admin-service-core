<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // For now, allow all authenticated users
        // You can add role-based logic here later
        return true;
    }
    
    /**
     * Get the companies that the user belongs to.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    /**
     * Get the tenants (companies) that the user can access.
     * Required by Filament's HasTenants interface.
     */
    public function getTenants(Panel $panel): array | Collection
    {
        return $this->companies;
    }
    
    /**
     * Determine if the user can access a specific tenant (company).
     * Required by Filament's HasTenants interface.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->companies->contains($tenant);
    }
    
    /**
     * Check if user is admin for a specific company.
     */
    public function isAdminFor(Company $company): bool
    {
        return $this->companies()
            ->where('company_id', $company->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }
    
    /**
     * Check if user is manager or admin for a specific company.
     */
    public function isManagerFor(Company $company): bool
    {
        return $this->companies()
            ->where('company_id', $company->id)
            ->wherePivotIn('role', ['admin', 'manager'])
            ->exists();
    }
}
