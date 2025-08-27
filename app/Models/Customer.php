<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected function casts(): array
    {
        return [
            'customer_since' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
