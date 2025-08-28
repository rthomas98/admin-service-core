<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Customer extends Model
{
    use HasFactory;
    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && Auth::user()->current_company_id) {
                $builder->where('customers.company_id', Auth::user()->current_company_id);
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
    ];

    protected function casts(): array
    {
        return [
            'customer_since' => 'date',
            'notification_preferences' => 'array',
            'notifications_enabled' => 'boolean',
            'sms_verified' => 'boolean',
            'sms_verified_at' => 'datetime',
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
        if (!$this->phone) {
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
        if (!$this->notifications_enabled) {
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
