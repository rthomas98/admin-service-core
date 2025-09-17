<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CompanyUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'portal_access',
        'email_verified_at',
        'last_login_at',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'portal_access' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCompanyOwner(): bool
    {
        return $this->role === 'company';
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'company', 'manager']);
    }

    public function canAccessPortal(): bool
    {
        return $this->is_active && $this->portal_access;
    }

    public function hasPermission(string $permission): bool
    {
        // Admin and Company Owner have all permissions
        if ($this->isAdmin() || $this->isCompanyOwner()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }
}
