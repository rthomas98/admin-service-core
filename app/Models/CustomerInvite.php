<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class CustomerInvite extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && Auth::user()->current_company_id) {
                $builder->where('customer_invites.company_id', Auth::user()->current_company_id);
            }
        });

        static::creating(function ($invite) {
            if (empty($invite->token)) {
                // Use cryptographically secure token generation
                $invite->token = bin2hex(random_bytes(32));
            }

            // Set default expiration if not provided
            if (empty($invite->expires_at)) {
                $invite->expires_at = now()->addDays(7);
            }

            // Set is_active to true by default
            if (! isset($invite->is_active)) {
                $invite->is_active = true;
            }
        });
    }

    protected $fillable = [
        'token',
        'email',
        'customer_id',
        'company_id',
        'template_id',
        'invited_by',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    protected $hidden = [
        'token', // Hide token from JSON serialization for security
    ];

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CustomerInviteTemplate::class, 'template_id');
    }

    // Query Scopes
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now())
            ->whereNull('accepted_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('expires_at', '>', now())
            ->whereNull('accepted_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereNotNull('accepted_at');
    }

    public function scopeForEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', strtolower(trim($email)));
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopePendingForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->forCustomer($customerId)->pending();
    }

    // Helper Methods
    public function isValid(): bool
    {
        return $this->is_active
            && $this->expires_at->isFuture()
            && is_null($this->accepted_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }

    public function accept(): void
    {
        $this->update(['accepted_at' => now()]);
    }

    public function markAsAccepted(): void
    {
        $this->update([
            'accepted_at' => now(),
            'is_active' => false,
        ]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function extendExpiration(int $days = 7): void
    {
        $this->update(['expires_at' => now()->addDays($days)]);
    }

    public function regenerateToken(): void
    {
        $this->update(['token' => bin2hex(random_bytes(32))]);
    }

    public function getExpirationStatusAttribute(): string
    {
        if ($this->isAccepted()) {
            return 'accepted';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'valid';
    }

    // Static Methods
    public static function generateInvite(string $email, int $companyId, ?int $customerId = null, int $daysValid = 7): self
    {
        return self::create([
            'email' => $email,
            'customer_id' => $customerId,
            'company_id' => $companyId,
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays($daysValid),
        ]);
    }

    // Validation Rules
    public static function validationRules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'company_id' => 'required|exists:companies,id',
            'expires_at' => 'required|date|after:now',
        ];
    }

    // Bulk Operations
    public static function createBulk(int $customerId, array $emails, int $invitedBy): array
    {
        $created = [];
        $skipped = [];

        \DB::transaction(function () use ($customerId, $emails, $invitedBy, &$created, &$skipped) {
            $customer = Customer::find($customerId);
            if (! $customer) {
                throw new \Exception('Customer not found');
            }

            foreach ($emails as $email) {
                $email = strtolower(trim($email));

                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped[] = ['email' => $email, 'reason' => 'Invalid email format'];

                    continue;
                }

                $existing = self::where('customer_id', $customerId)
                    ->where('email', $email)
                    ->where('is_active', true)
                    ->whereNull('accepted_at')
                    ->exists();

                if ($existing) {
                    $skipped[] = ['email' => $email, 'reason' => 'Already has active invitation'];

                    continue;
                }

                $created[] = self::create([
                    'customer_id' => $customerId,
                    'company_id' => $customer->company_id,
                    'email' => $email,
                    'invited_by' => $invitedBy,
                    'token' => bin2hex(random_bytes(32)),
                    'expires_at' => now()->addDays(7),
                    'is_active' => true,
                ]);
            }
        });

        // Queue emails for created invitations
        foreach ($created as $invite) {
            // Dispatch job if it exists, otherwise send directly
            if (class_exists('\App\Jobs\SendInvitationJob')) {
                \App\Jobs\SendInvitationJob::dispatch($invite);
            }
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    public static function deactivateAllForCustomer(int $customerId): int
    {
        return self::where('customer_id', $customerId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    public static function resendBulk(array $inviteIds): array
    {
        $sent = [];
        $failed = [];

        foreach ($inviteIds as $inviteId) {
            $invite = self::find($inviteId);

            if (! $invite || $invite->isAccepted()) {
                $failed[] = $inviteId;

                continue;
            }

            $invite->regenerateToken();
            $invite->extendExpiration(7);

            if (class_exists('\App\Jobs\SendInvitationJob')) {
                \App\Jobs\SendInvitationJob::dispatch($invite);
            }

            $sent[] = $inviteId;
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    // Statistics
    public static function getStatistics(?int $customerId = null): array
    {
        $query = self::query();

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        $total = $query->count();
        $accepted = $query->clone()->whereNotNull('accepted_at')->count();
        $pending = $query->clone()->pending()->count();
        $expired = $query->clone()->expired()->count();

        return [
            'total' => $total,
            'accepted' => $accepted,
            'pending' => $pending,
            'expired' => $expired,
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 2) : 0,
        ];
    }

    // Cleanup Methods
    public static function cleanupExpired(): int
    {
        return self::expired()
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    // Find Methods
    public static function findByValidToken(string $token): ?self
    {
        return self::where('token', $token)
            ->active()
            ->first();
    }
}
