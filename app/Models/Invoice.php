<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
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
        'status' => InvoiceStatus::class,
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
     * Get the work order for this invoice.
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
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
     * Get the invoice items for this invoice.
     */
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Check if invoice is paid in full.
     */
    public function isPaid(): bool
    {
        return $this->status === InvoiceStatus::Paid;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! $this->isPaid();
    }

    /**
     * Update invoice totals based on items.
     */
    public function updateTotals(): void
    {
        $subtotal = 0;
        $taxAmount = 0;

        foreach ($this->items as $item) {
            $item->total = $item->calculateTotal();
            $item->save();

            $subtotal += $item->quantity * $item->unit_price;
            $taxAmount += $item->tax_amount;
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total_amount = $subtotal + $taxAmount - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->amount_paid;

        // Update status if overdue
        if ($this->isOverdue() && $this->status !== InvoiceStatus::Paid) {
            $this->status = InvoiceStatus::Overdue;
        }

        $this->save();
    }

    /**
     * Mark invoice as sent.
     */
    public function markAsSent(): void
    {
        $this->status = InvoiceStatus::Sent;
        $this->sent_date = now();
        $this->save();
    }

    /**
     * Record a payment for this invoice.
     */
    public function recordPayment(float $amount): void
    {
        $this->amount_paid += $amount;
        $this->balance_due = $this->total_amount - $this->amount_paid;

        if ($this->balance_due <= 0) {
            $this->status = InvoiceStatus::Paid;
            $this->paid_date = now();
        } elseif ($this->amount_paid > 0) {
            $this->status = InvoiceStatus::PartiallyPaid;
        }

        $this->save();
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

        return $prefix.$date.str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create invoice from work order.
     */
    public static function createFromWorkOrder(WorkOrder $workOrder): self
    {
        $invoice = new self([
            'company_id' => $workOrder->company_id,
            'customer_id' => $workOrder->customer_id,
            'service_order_id' => $workOrder->service_order_id,
            'invoice_number' => self::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30), // Default NET 30
            'status' => InvoiceStatus::Draft,
            'tax_rate' => 8.75, // Default tax rate - should be configurable
        ]);

        $invoice->save();

        // Create invoice items from work order
        $items = InvoiceItem::createFromWorkOrder($workOrder);
        foreach ($items as $itemData) {
            $itemData['invoice_id'] = $invoice->id;
            $item = new InvoiceItem($itemData);
            $item->total = $item->calculateTotal();
            $item->save();
        }

        // Update invoice totals
        $invoice->load('items');
        $invoice->updateTotals();

        return $invoice;
    }
}
