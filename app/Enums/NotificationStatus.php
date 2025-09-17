<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case SCHEDULED = 'scheduled';
    case DELIVERED = 'delivered';
    case BOUNCED = 'bounced';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SENT => 'Sent',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::SCHEDULED => 'Scheduled',
            self::DELIVERED => 'Delivered',
            self::BOUNCED => 'Bounced',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::SENT => 'info',
            self::FAILED => 'danger',
            self::CANCELLED => 'secondary',
            self::SCHEDULED => 'primary',
            self::DELIVERED => 'success',
            self::BOUNCED => 'danger',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::SENT => 'heroicon-o-paper-airplane',
            self::FAILED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-no-symbol',
            self::SCHEDULED => 'heroicon-o-calendar',
            self::DELIVERED => 'heroicon-o-check-circle',
            self::BOUNCED => 'heroicon-o-exclamation-triangle',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
