<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'company_id',
        'title',
        'description',
        'status',
        'priority',
        'category',
        'details',
        'requested_date',
        'scheduled_date',
        'completed_date',
        'assigned_to',
        'internal_notes',
        'customer_notes',
        'estimated_cost',
        'actual_cost',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceRequestStatus::class,
            'details' => 'array',
            'requested_date' => 'datetime',
            'scheduled_date' => 'datetime',
            'completed_date' => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ServiceRequestAttachment::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ServiceRequestActivity::class)->orderBy('performed_at', 'desc');
    }

    public function publicActivities(): HasMany
    {
        return $this->hasMany(ServiceRequestActivity::class)
            ->where('is_internal', false)
            ->orderBy('performed_at', 'desc');
    }

    public function internalActivities(): HasMany
    {
        return $this->hasMany(ServiceRequestActivity::class)
            ->where('is_internal', true)
            ->orderBy('performed_at', 'desc');
    }

    // Query Scopes
    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByStatus(Builder $query, ServiceRequestStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority(Builder $query, string $priority): Builder
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeScheduledBetween(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('scheduled_date', [$startDate, $endDate]);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('scheduled_date', '<', now())
            ->whereIn('status', [ServiceRequestStatus::PENDING, ServiceRequestStatus::IN_PROGRESS]);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('customer_notes', 'like', "%{$search}%")
                ->orWhere('internal_notes', 'like', "%{$search}%");
        });
    }

    // Helper Methods
    public function isPending(): bool
    {
        return $this->status === ServiceRequestStatus::PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === ServiceRequestStatus::IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status === ServiceRequestStatus::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === ServiceRequestStatus::CANCELLED;
    }

    public function isOnHold(): bool
    {
        return $this->status === ServiceRequestStatus::ON_HOLD;
    }

    public function isOverdue(): bool
    {
        if (! $this->scheduled_date) {
            return false;
        }

        return $this->scheduled_date->isPast() &&
               in_array($this->status, [ServiceRequestStatus::PENDING, ServiceRequestStatus::IN_PROGRESS]);
    }

    public function getDaysUntilScheduledAttribute(): ?int
    {
        if (! $this->scheduled_date) {
            return null;
        }

        return now()->diffInDays($this->scheduled_date, false);
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'urgent' => 'danger',
            default => 'secondary',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return ucfirst($this->priority);
    }

    // Enhanced Helper Methods
    public function getAttachmentCount(): int
    {
        return $this->attachments()->count();
    }

    public function getActivityCount(): int
    {
        return $this->activities()->count();
    }

    public function getPublicActivityCount(): int
    {
        return $this->publicActivities()->count();
    }

    public function hasAttachments(): bool
    {
        return $this->getAttachmentCount() > 0;
    }

    public function getRecentActivity(int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return $this->activities()->limit($limit)->get();
    }

    public function getStatusHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->activities()
            ->where('activity_type', ServiceRequestActivity::TYPE_STATUS_CHANGED)
            ->get();
    }

    // Status Management with Activity Logging
    public function markInProgress(?int $assignedTo = null, ?User $user = null): void
    {
        $oldStatus = $this->status->value;
        $this->update([
            'status' => ServiceRequestStatus::IN_PROGRESS,
            'assigned_to' => $assignedTo ?? $this->assigned_to,
        ]);

        if ($user) {
            ServiceRequestActivity::logStatusChange(
                $this,
                $user,
                $oldStatus,
                ServiceRequestStatus::IN_PROGRESS->value
            );
        }
    }

    public function markCompleted(?User $user = null): void
    {
        $oldStatus = $this->status->value;
        $this->update([
            'status' => ServiceRequestStatus::COMPLETED,
            'completed_date' => now(),
        ]);

        if ($user) {
            ServiceRequestActivity::logStatusChange(
                $this,
                $user,
                $oldStatus,
                ServiceRequestStatus::COMPLETED->value
            );
        }
    }

    public function markCancelled(?User $user = null): void
    {
        $oldStatus = $this->status->value;
        $this->update([
            'status' => ServiceRequestStatus::CANCELLED,
        ]);

        if ($user) {
            ServiceRequestActivity::logStatusChange(
                $this,
                $user,
                $oldStatus,
                ServiceRequestStatus::CANCELLED->value
            );
        }
    }

    public function putOnHold(?User $user = null): void
    {
        $oldStatus = $this->status->value;
        $this->update([
            'status' => ServiceRequestStatus::ON_HOLD,
        ]);

        if ($user) {
            ServiceRequestActivity::logStatusChange(
                $this,
                $user,
                $oldStatus,
                ServiceRequestStatus::ON_HOLD->value
            );
        }
    }

    public function assignTo(int $userId, ?User $assignedBy = null): void
    {
        $previousAssignee = $this->assignedTo;
        $newAssignee = User::find($userId);

        $this->update(['assigned_to' => $userId]);

        if ($assignedBy && $newAssignee) {
            ServiceRequestActivity::logAssignment(
                $this,
                $assignedBy,
                $newAssignee,
                $previousAssignee
            );
        }
    }

    public function unassign(?User $unassignedBy = null): void
    {
        $previousAssignee = $this->assignedTo;
        $this->update(['assigned_to' => null]);

        if ($unassignedBy) {
            ServiceRequestActivity::logAssignment(
                $this,
                $unassignedBy,
                null,
                $previousAssignee
            );
        }
    }

    public function schedule(\DateTime $scheduledDate, ?User $scheduledBy = null): void
    {
        $this->update(['scheduled_date' => $scheduledDate]);

        if ($scheduledBy) {
            ServiceRequestActivity::logScheduled($this, $scheduledBy, $scheduledDate);
        }
    }

    public function addComment(string $comment, User $user, bool $isInternal = false): void
    {
        ServiceRequestActivity::logComment($this, $user, $comment, $isInternal);
    }

    // Model Events
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('company', function (Builder $builder) {
            if (Auth::check() && Auth::user()->current_company_id) {
                $builder->where('service_requests.company_id', Auth::user()->current_company_id);
            }
        });

        // Log creation activity
        static::created(function (ServiceRequest $serviceRequest) {
            if (Auth::check()) {
                ServiceRequestActivity::logCreated($serviceRequest, Auth::user());
            }
        });
    }

    // Validation Rules
    public static function validationRules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'company_id' => 'required|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|string|in:pending,in_progress,completed,cancelled,on_hold',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
            'requested_date' => 'nullable|date',
            'scheduled_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
        ];
    }
}
