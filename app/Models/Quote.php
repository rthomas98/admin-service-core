<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Quote extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'company_id',
        'customer_id',
        'quote_number',
        'quote_date',
        'valid_until',
        'items',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'description',
        'terms_conditions',
        'notes',
        'delivery_address',
        'delivery_city',
        'delivery_parish',
        'delivery_postal_code',
        'requested_delivery_date',
        'requested_pickup_date',
        'accepted_date',
        'converted_service_order_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'requested_delivery_date' => 'date',
        'requested_pickup_date' => 'date',
        'accepted_date' => 'date',
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns the quote.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the customer for this quote.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the service order this quote was converted to.
     */
    public function convertedServiceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class, 'converted_service_order_id');
    }

    /**
     * Check if quote is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if quote is expired.
     */
    public function isExpired(): bool
    {
        return $this->valid_until->isPast() || $this->status === 'expired';
    }

    /**
     * Check if quote was converted to service order.
     */
    public function isConverted(): bool
    {
        return !is_null($this->converted_service_order_id);
    }

    /**
     * Get the full delivery address.
     */
    public function getFullDeliveryAddressAttribute(): string
    {
        $parts = array_filter([
            $this->delivery_address,
            $this->delivery_city,
            $this->delivery_parish,
            $this->delivery_postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Generate unique quote number.
     */
    public static function generateQuoteNumber(): string
    {
        $prefix = 'QUO';
        $date = now()->format('ymd');
        $lastQuote = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastQuote ? 
            intval(substr($lastQuote->quote_number, -3)) + 1 : 1;

        return $prefix . $date . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }
}
