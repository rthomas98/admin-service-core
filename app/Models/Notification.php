<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'channel',
        'category',
        'recipient_type',
        'recipient_id',
        'recipient_email',
        'recipient_phone',
        'subject',
        'message',
        'data',
        'status',
        'scheduled_at',
        'sent_at',
        'read_at',
        'error_message',
        'retry_count',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'category' => NotificationCategory::class,
        'status' => NotificationStatus::class,
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where('notifications.company_id', auth()->user()->company_id);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::PENDING);
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::SENT);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::FAILED);
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::SCHEDULED)
                     ->where('scheduled_at', '>', now());
    }

    public function scopeDueForSending(Builder $query): Builder
    {
        return $query->where('status', NotificationStatus::SCHEDULED)
                     ->where('scheduled_at', '<=', now());
    }

    public function scopeForCategory(Builder $query, NotificationCategory $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeForType(Builder $query, NotificationType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    // Methods
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => NotificationStatus::FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    public function canRetry(): bool
    {
        return $this->retry_count < 3 && $this->status === NotificationStatus::FAILED;
    }

    public function shouldSendNow(): bool
    {
        return $this->status === NotificationStatus::PENDING || 
               ($this->status === NotificationStatus::SCHEDULED && $this->scheduled_at <= now());
    }
}