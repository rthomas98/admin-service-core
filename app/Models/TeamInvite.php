<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'email',
        'name',
        'role',
        'token',
        'invited_by',
        'expires_at',
        'accepted_at',
        'message',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'permissions' => 'array',
        ];
    }

    /**
     * Get the company that the invite is for.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who sent the invite.
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if the invite is valid (not expired and not accepted).
     */
    public function isValid(): bool
    {
        return ! $this->isExpired() && ! $this->isAccepted();
    }

    /**
     * Check if the invite has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invite has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /**
     * Mark the invite as accepted.
     */
    public function markAsAccepted(): void
    {
        $this->update(['accepted_at' => now()]);
    }

    /**
     * Generate a new unique token.
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Generate an invitation for a team member.
     */
    public static function generateInvite(array $data): self
    {
        return self::create([
            'company_id' => $data['company_id'] ?? null,
            'email' => $data['email'],
            'name' => $data['name'] ?? null,
            'role' => $data['role'],
            'token' => self::generateToken(),
            'invited_by' => auth()->id() ?? $data['invited_by'],
            'expires_at' => now()->addDays($data['expires_in_days'] ?? 7),
            'message' => $data['message'] ?? null,
            'permissions' => $data['permissions'] ?? null,
        ]);
    }

    /**
     * Regenerate the token for this invite.
     */
    public function regenerateToken(): void
    {
        $this->update(['token' => self::generateToken()]);
    }

    /**
     * Extend the expiration date.
     */
    public function extendExpiration(int $days = 7): void
    {
        $this->update(['expires_at' => now()->addDays($days)]);
    }

    /**
     * Get the status of the invitation.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isAccepted()) {
            return 'accepted';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'pending';
    }

    /**
     * Scope for valid invites.
     */
    public function scopeValid($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope for expired invites.
     */
    public function scopeExpired($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope for accepted invites.
     */
    public function scopeAccepted($query)
    {
        return $query->whereNotNull('accepted_at');
    }

    /**
     * Get available roles for team members.
     */
    public static function getAvailableRoles(): array
    {
        return [
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'dispatcher' => 'Dispatcher',
            'driver' => 'Driver',
            'accountant' => 'Accountant',
            'customer_service' => 'Customer Service',
            'viewer' => 'Viewer (Read-only)',
        ];
    }

    /**
     * Get role description.
     */
    public function getRoleDescription(): string
    {
        $roles = self::getAvailableRoles();

        return $roles[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
    }
}
