<?php

namespace App\Models;

use App\Enums\TimePeriod;
use App\Enums\WorkOrderAction;
use App\Enums\WorkOrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'ticket_number',
        'po_number',
        'service_date',
        'time_on_site',
        'time_off_site',
        'time_on_site_period',
        'time_off_site_period',
        'truck_number',
        'dispatch_number',
        'driver_id',
        'customer_id',
        'customer_name',
        'address',
        'city',
        'state',
        'zip',
        'action',
        'container_size',
        'waste_type',
        'service_description',
        'container_delivered',
        'container_picked_up',
        'disposal_id',
        'disposal_ticket',
        'cod_amount',
        'cod_signature',
        'comments',
        'customer_signature',
        'customer_signature_date',
        'driver_signature',
        'driver_signature_date',
        'status',
        'completed_at',
        'service_order_id',
        'equipment_id',
        'vehicle_id',
        'scheduled_date',
        'start_date',
        'end_date',
        'estimated_cost',
        'invoice_id',
        'invoiced_at',
    ];

    protected $casts = [
        'service_date' => 'date',
        'scheduled_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'cod_amount' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'customer_signature_date' => 'datetime',
        'driver_signature_date' => 'datetime',
        'completed_at' => 'datetime',
        'invoiced_at' => 'datetime',
        'status' => WorkOrderStatus::class,
        'action' => WorkOrderAction::class,
        'time_on_site_period' => TimePeriod::class,
        'time_off_site_period' => TimePeriod::class,
        'time_on_site' => 'datetime:H:i',
        'time_off_site' => 'datetime:H:i',
    ];

    protected $with = ['customer', 'driver'];

    protected static function booted()
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && Auth::user()->current_company_id) {
                $builder->where('work_orders.company_id', Auth::user()->current_company_id);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->zip,
        ]);

        return implode(', ', $parts);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function requiresCOD(): bool
    {
        return $this->cod_amount > 0;
    }

    public function hasCustomerSignature(): bool
    {
        return ! empty($this->customer_signature);
    }

    public function hasDriverSignature(): bool
    {
        return ! empty($this->driver_signature);
    }

    public function isFullySigned(): bool
    {
        return $this->hasCustomerSignature() && $this->hasDriverSignature();
    }

    // Query Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [WorkOrderStatus::DRAFT->value, WorkOrderStatus::IN_PROGRESS->value]);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', WorkOrderStatus::COMPLETED->value);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', WorkOrderStatus::CANCELLED->value);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForDriver(Builder $query, int $driverId): Builder
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('service_date', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('service_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('service_date', now()->month)
            ->whereYear('service_date', now()->year);
    }

    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('service_date', [$startDate, $endDate]);
    }

    public function scopeRequiresCOD(Builder $query): Builder
    {
        return $query->where('cod_amount', '>', 0);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('ticket_number', 'like', "%{$search}%")
                ->orWhere('po_number', 'like', "%{$search}%")
                ->orWhere('dispatch_number', 'like', "%{$search}%")
                ->orWhere('customer_name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        });
    }

    // Validation Rules
    public static function validationRules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
            'service_date' => 'required|date',
            'status' => 'required|in:'.implode(',', array_column(WorkOrderStatus::cases(), 'value')),
            'action' => 'required|in:'.implode(',', array_column(WorkOrderAction::cases(), 'value')),
            'time_on_site_period' => 'nullable|in:AM,PM',
            'time_off_site_period' => 'nullable|in:AM,PM',
            'driver_id' => 'nullable|exists:drivers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'cod_amount' => 'nullable|numeric|min:0',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
        ];
    }
}
