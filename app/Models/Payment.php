<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'invoice_id',
        'customer_id',
        'amount',
        'payment_method',
        'transaction_id',
        'reference_number',
        'payment_date',
        'processed_datetime',
        'status',
        'gateway',
        'gateway_transaction_id',
        'fee_amount',
        'net_amount',
        'gateway_response',
        'notes',
        'check_number',
        'check_date',
        'bank_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'payment_date' => 'date',
        'check_date' => 'date',
        'processed_datetime' => 'datetime',
        'gateway_response' => 'array',
    ];

    /**
     * Get the company that owns the payment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the invoice for this payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the customer for this payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if payment is electronic.
     */
    public function isElectronic(): bool
    {
        return in_array($this->payment_method, ['credit_card', 'debit_card', 'bank_transfer', 'paypal', 'online']);
    }
}
