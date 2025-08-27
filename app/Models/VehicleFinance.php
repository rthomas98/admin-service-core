<?php

namespace App\Models;

use App\Enums\FinanceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VehicleFinance extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'financeable_type',
        'financeable_id',
        'finance_company_id',
        'account_number',
        'finance_type',
        'start_date',
        'end_date',
        'monthly_payment',
        'total_amount',
        'down_payment',
        'interest_rate',
        'term_months',
        'reference_number',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'finance_type' => FinanceType::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_payment' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'term_months' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'finance_type' => FinanceType::Loan,
        'is_active' => true,
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByType(Builder $query, FinanceType $type): Builder
    {
        return $query->where('finance_type', $type);
    }

    public function scopeExpiring(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('end_date', [
            now(),
            now()->addDays($days),
        ]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('end_date', '<', now())
            ->where('is_active', true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function financeCompany(): BelongsTo
    {
        return $this->belongsTo(FinanceCompany::class);
    }

    public function financeable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDisplayNameAttribute(): string
    {
        return sprintf(
            '%s - %s (%s)',
            $this->financeCompany?->name ?? 'Unknown Company',
            $this->finance_type->label(),
            $this->account_number ?? $this->reference_number ?? 'N/A'
        );
    }

    public function getTitleAttribute(): string
    {
        return $this->display_name;
    }

    public function getRemainingBalanceAttribute(): ?float
    {
        if (!$this->total_amount || !$this->monthly_payment || !$this->start_date) {
            return null;
        }

        $monthsElapsed = now()->diffInMonths($this->start_date);
        $paidAmount = min($monthsElapsed * $this->monthly_payment, $this->total_amount);

        return max(0, $this->total_amount - $paidAmount);
    }

    public function getRemainingMonthsAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return max(0, now()->diffInMonths($this->end_date, false));
    }

    public function getDaysUntilEndAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    public function getStatusAttribute(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        if (!$this->end_date) {
            return 'active';
        }

        $days = $this->days_until_end;

        if ($days < 0) {
            return 'expired';
        }

        if ($days <= 30) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function getTotalInterestAttribute(): ?float
    {
        if (!$this->total_amount || !$this->down_payment) {
            return null;
        }

        $principalAmount = $this->total_amount - $this->down_payment;

        if (!$this->interest_rate || !$this->term_months) {
            return null;
        }

        $monthlyRate = $this->interest_rate / 100 / 12;
        $totalPayments = $this->monthly_payment * $this->term_months;

        return max(0, $totalPayments - $principalAmount);
    }

    public function getProgressPercentageAttribute(): ?float
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays(now());

        if ($totalDays <= 0) {
            return 100.0;
        }

        return min(100.0, max(0.0, ($elapsedDays / $totalDays) * 100));
    }

    public function isExpiring(int $days = 30): bool
    {
        if (!$this->end_date) {
            return false;
        }

        $daysUntilEnd = $this->days_until_end;

        return $daysUntilEnd !== null && $daysUntilEnd <= $days && $daysUntilEnd >= 0;
    }

    public function isOverdue(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->isAfter($this->end_date) && $this->is_active;
    }

    public function markAsCompleted(?string $notes = null): void
    {
        $this->update([
            'is_active' => false,
            'notes' => $notes ? $this->notes . "\n" . $notes : $this->notes,
        ]);
    }

    public static function getValidationRules(): array
    {
        return [
            'financeable_type' => ['required', 'string'],
            'financeable_id' => ['required', 'integer'],
            'finance_company_id' => ['required', 'exists:finance_companies,id'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'finance_type' => ['required', 'string', 'in:' . implode(',', array_column(FinanceType::cases(), 'value'))],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'monthly_payment' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'term_months' => ['nullable', 'integer', 'min:1'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}