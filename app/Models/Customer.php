<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted(): void
    {
        // Use Filament's tenant system instead of current_company_id
        static::addGlobalScope('company', function (Builder $builder) {
            // Only apply scope in Filament admin context
            if (class_exists(\Filament\Facades\Filament::class)) {
                $tenant = \Filament\Facades\Filament::getTenant();
                if ($tenant) {
                    $builder->where('customers.company_id', $tenant->id);
                }
            }
        });

        // Optional: Add logging for debugging purposes (can be removed in production)
        static::saving(function ($customer) {
            if (config('app.debug')) {
                \Log::debug('Customer saving', [
                    'id' => $customer->id,
                    'company_id' => $customer->company_id,
                    'dirty_attributes' => array_keys($customer->getDirty()),
                ]);
            }
        });
    }

    protected $fillable = [
        'company_id',
        'external_id',
        'customer_since',
        'first_name',
        'last_name',
        'name',
        'organization',
        'emails',
        'phone',
        'phone_ext',
        'secondary_phone',
        'secondary_phone_ext',
        'fax',
        'fax_ext',
        'address',
        'secondary_address',
        'city',
        'state',
        'zip',
        'county',
        'external_message',
        'internal_memo',
        'delivery_method',
        'referral',
        'customer_number',
        'tax_exemption_details',
        'tax_exempt_reason',
        'divisions',
        'business_type',
        'tax_code_name',
        'notification_preferences',
        'notifications_enabled',
        'preferred_notification_method',
        'sms_number',
        'sms_verified',
        'sms_verified_at',
        'portal_access',
        'portal_password',
        'email_verified_at',
        'remember_token',
    ];

    protected $appends = ['full_name'];

    protected function casts(): array
    {
        return [
            'customer_since' => 'date',
            'emails' => 'array',
            'notification_preferences' => 'array',
            'notifications_enabled' => 'boolean',
            'sms_verified' => 'boolean',
            'sms_verified_at' => 'datetime',
            'portal_access' => 'boolean',
            'email_verified_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'recipient');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(CustomerInvite::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function getFullNameAttribute(): ?string
    {
        if ($this->organization) {
            return $this->organization;
        }

        if ($this->name) {
            return $this->name;
        }

        return trim("{$this->first_name} {$this->last_name}") ?: null;
    }

    public function getDisplayPhoneAttribute(): ?string
    {
        if (! $this->phone) {
            return null;
        }

        return $this->phone_ext ? "{$this->phone} ext. {$this->phone_ext}" : $this->phone;
    }

    // Query Scopes
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('organization', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhere('customer_number', 'like', "%{$search}%");
        });
    }

    public function scopeInZip(Builder $query, string $zip): Builder
    {
        return $query->where('zip', $zip);
    }

    public function scopeInCity(Builder $query, string $city): Builder
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeInState(Builder $query, string $state): Builder
    {
        return $query->where('state', $state);
    }

    public function scopeInCounty(Builder $query, string $county): Builder
    {
        return $query->where('county', 'like', "%{$county}%");
    }

    public function scopeTaxExempt(Builder $query): Builder
    {
        return $query->whereNotNull('tax_exemption_details');
    }

    // Notification Preference Methods
    public function isNotificationEnabled(NotificationCategory $category): bool
    {
        if (! $this->notifications_enabled) {
            return false;
        }

        $preferences = $this->notification_preferences ?? [];

        return $preferences[$category->value]['enabled'] ?? true;
    }

    public function getNotificationMethod(NotificationCategory $category): string
    {
        $preferences = $this->notification_preferences ?? [];

        return $preferences[$category->value]['method'] ?? $this->preferred_notification_method;
    }

    public function canReceiveSms(): bool
    {
        return $this->sms_number && $this->sms_verified;
    }

    public function getNotificationEmail(): ?string
    {
        // Try to get the first email from the emails field, or use a default email field
        if ($this->emails) {
            $emails = is_array($this->emails) ? $this->emails : json_decode($this->emails, true);

            return $emails[0] ?? null;
        }

        return null;
    }

    public function updateNotificationPreferences(array $preferences): void
    {
        $this->update(['notification_preferences' => $preferences]);
    }

    // Authentication Methods
    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthPassword(): string
    {
        return $this->portal_password;
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->getNotificationEmail();
    }

    public function hasPortalAccess(): bool
    {
        return $this->portal_access && ! empty($this->portal_password);
    }

    public function enablePortalAccess(string $password): void
    {
        $this->update([
            'portal_access' => true,
            'portal_password' => bcrypt($password),
        ]);
    }

    public function disablePortalAccess(): void
    {
        $this->update([
            'portal_access' => false,
            'portal_password' => null,
            'remember_token' => null,
        ]);
    }

    // Validation Rules
    public static function validationRules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'name' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_ext' => 'nullable|string|max:10',
            'secondary_phone' => 'nullable|string|max:20',
            'secondary_phone_ext' => 'nullable|string|max:10',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
            'customer_since' => 'nullable|date',
        ];
    }
}
