<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequestActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'activity_type',
        'title',
        'description',
        'old_values',
        'new_values',
        'is_internal',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'is_internal' => 'boolean',
            'performed_at' => 'datetime',
        ];
    }

    // Relationships
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Activity Types
    public const TYPE_CREATED = 'created';

    public const TYPE_STATUS_CHANGED = 'status_changed';

    public const TYPE_ASSIGNED = 'assigned';

    public const TYPE_UNASSIGNED = 'unassigned';

    public const TYPE_COMMENT = 'comment';

    public const TYPE_ATTACHMENT_ADDED = 'attachment_added';

    public const TYPE_ATTACHMENT_REMOVED = 'attachment_removed';

    public const TYPE_SCHEDULED = 'scheduled';

    public const TYPE_COST_UPDATED = 'cost_updated';

    public const TYPE_PRIORITY_CHANGED = 'priority_changed';

    public const TYPE_CATEGORY_CHANGED = 'category_changed';

    public const TYPE_UPDATED = 'updated';

    // Helper Methods
    public function getActivityIcon(): string
    {
        return match ($this->activity_type) {
            self::TYPE_CREATED => 'heroicon-o-plus-circle',
            self::TYPE_STATUS_CHANGED => 'heroicon-o-arrow-path',
            self::TYPE_ASSIGNED => 'heroicon-o-user-plus',
            self::TYPE_UNASSIGNED => 'heroicon-o-user-minus',
            self::TYPE_COMMENT => 'heroicon-o-chat-bubble-left',
            self::TYPE_ATTACHMENT_ADDED => 'heroicon-o-paper-clip',
            self::TYPE_ATTACHMENT_REMOVED => 'heroicon-o-x-circle',
            self::TYPE_SCHEDULED => 'heroicon-o-calendar',
            self::TYPE_COST_UPDATED => 'heroicon-o-currency-dollar',
            self::TYPE_PRIORITY_CHANGED => 'heroicon-o-exclamation-triangle',
            self::TYPE_CATEGORY_CHANGED => 'heroicon-o-tag',
            self::TYPE_UPDATED => 'heroicon-o-pencil',
            default => 'heroicon-o-information-circle',
        };
    }

    public function getActivityColor(): string
    {
        return match ($this->activity_type) {
            self::TYPE_CREATED => 'success',
            self::TYPE_STATUS_CHANGED => 'warning',
            self::TYPE_ASSIGNED => 'info',
            self::TYPE_UNASSIGNED => 'secondary',
            self::TYPE_COMMENT => 'primary',
            self::TYPE_ATTACHMENT_ADDED => 'info',
            self::TYPE_ATTACHMENT_REMOVED => 'danger',
            self::TYPE_SCHEDULED => 'info',
            self::TYPE_COST_UPDATED => 'warning',
            self::TYPE_PRIORITY_CHANGED => 'warning',
            self::TYPE_CATEGORY_CHANGED => 'info',
            self::TYPE_UPDATED => 'secondary',
            default => 'secondary',
        };
    }

    // Static Methods for Creating Activities
    public static function logCreated(ServiceRequest $serviceRequest, User $user): void
    {
        static::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_CREATED,
            'title' => 'Service request created',
            'description' => "Service request '{$serviceRequest->title}' was created",
            'is_internal' => false,
            'performed_at' => now(),
        ]);
    }

    public static function logStatusChange(ServiceRequest $serviceRequest, User $user, string $oldStatus, string $newStatus): void
    {
        static::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_STATUS_CHANGED,
            'title' => 'Status changed',
            'description' => "Status changed from {$oldStatus} to {$newStatus}",
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'is_internal' => false,
            'performed_at' => now(),
        ]);
    }

    public static function logAssignment(ServiceRequest $serviceRequest, User $user, ?User $assignedTo = null, ?User $previousAssignee = null): void
    {
        if ($assignedTo) {
            static::create([
                'service_request_id' => $serviceRequest->id,
                'user_id' => $user->id,
                'activity_type' => self::TYPE_ASSIGNED,
                'title' => 'Request assigned',
                'description' => "Request assigned to {$assignedTo->name}",
                'new_values' => ['assigned_to' => $assignedTo->name],
                'old_values' => $previousAssignee ? ['assigned_to' => $previousAssignee->name] : null,
                'is_internal' => true,
                'performed_at' => now(),
            ]);
        } else {
            static::create([
                'service_request_id' => $serviceRequest->id,
                'user_id' => $user->id,
                'activity_type' => self::TYPE_UNASSIGNED,
                'title' => 'Request unassigned',
                'description' => 'Request was unassigned',
                'old_values' => $previousAssignee ? ['assigned_to' => $previousAssignee->name] : null,
                'is_internal' => true,
                'performed_at' => now(),
            ]);
        }
    }

    public static function logComment(ServiceRequest $serviceRequest, User $user, string $comment, bool $isInternal = false): void
    {
        static::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_COMMENT,
            'title' => $isInternal ? 'Internal note added' : 'Comment added',
            'description' => $comment,
            'is_internal' => $isInternal,
            'performed_at' => now(),
        ]);
    }

    public static function logAttachmentAdded(ServiceRequest $serviceRequest, User $user, string $filename): void
    {
        static::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_ATTACHMENT_ADDED,
            'title' => 'File attached',
            'description' => "File '{$filename}' was uploaded",
            'is_internal' => false,
            'performed_at' => now(),
        ]);
    }

    public static function logScheduled(ServiceRequest $serviceRequest, User $user, ?\DateTime $scheduledDate): void
    {
        static::create([
            'service_request_id' => $serviceRequest->id,
            'user_id' => $user->id,
            'activity_type' => self::TYPE_SCHEDULED,
            'title' => 'Request scheduled',
            'description' => $scheduledDate
                ? "Request scheduled for {$scheduledDate->format('M j, Y g:i A')}"
                : 'Request scheduling was removed',
            'new_values' => ['scheduled_date' => $scheduledDate?->toISOString()],
            'is_internal' => false,
            'performed_at' => now(),
        ]);
    }

    // Validation Rules
    public static function validationRules(): array
    {
        return [
            'service_request_id' => 'required|exists:service_requests,id',
            'user_id' => 'required|exists:users,id',
            'activity_type' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_internal' => 'boolean',
        ];
    }
}
