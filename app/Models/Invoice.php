<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'service_order_id',
        'customer_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'status',
        'sent_date',
        'paid_date',
        'line_items',
        'notes',
        'terms_conditions',
        'billing_address',
        'billing_city',
        'billing_parish',
        'billing_postal_code',
        'is_recurring',
        'recurring_frequency',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'line_items' => 'array',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the company that owns the invoice.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the service order for this invoice.
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    /**
     * Get the customer for this invoice.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the payments for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if invoice is paid in full.
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && !$this->isPaid();
    }

    /**
     * Get the full billing address.
     */
    public function getFullBillingAddressAttribute(): string
    {
        $parts = array_filter([
            $this->billing_address,
            $this->billing_city,
            $this->billing_parish,
            $this->billing_postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Generate unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('ymd');
        $lastInvoice = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? 
            intval(substr($lastInvoice->invoice_number, -3)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
