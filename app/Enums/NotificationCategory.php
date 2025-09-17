<?php

namespace App\Enums;

enum NotificationCategory: string
{
    case SERVICE_REMINDER = 'service_reminder';
    case PAYMENT_DUE = 'payment_due';
    case EMERGENCY = 'emergency';
    case DISPATCH = 'dispatch';
    case MARKETING = 'marketing';
    case SYSTEM_UPDATE = 'system_update';
    case INVOICE = 'invoice';
    case QUOTE = 'quote';
    case MAINTENANCE = 'maintenance';
    case DELIVERY = 'delivery';
    case PICKUP = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::SERVICE_REMINDER => 'Service Reminder',
            self::PAYMENT_DUE => 'Payment Due',
            self::EMERGENCY => 'Emergency Alert',
            self::DISPATCH => 'Dispatch Notification',
            self::MARKETING => 'Marketing Message',
            self::SYSTEM_UPDATE => 'System Update',
            self::INVOICE => 'Invoice',
            self::QUOTE => 'Quote',
            self::MAINTENANCE => 'Maintenance',
            self::DELIVERY => 'Delivery',
            self::PICKUP => 'Pickup',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::EMERGENCY => 'danger',
            self::PAYMENT_DUE => 'warning',
            self::SERVICE_REMINDER => 'info',
            self::DISPATCH => 'primary',
            self::MARKETING => 'success',
            self::SYSTEM_UPDATE => 'secondary',
            self::INVOICE => 'warning',
            self::QUOTE => 'info',
            self::MAINTENANCE => 'warning',
            self::DELIVERY => 'success',
            self::PICKUP => 'primary',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::EMERGENCY => 1,
            self::DISPATCH => 2,
            self::PAYMENT_DUE => 3,
            self::SERVICE_REMINDER => 4,
            self::MAINTENANCE => 5,
            self::DELIVERY, self::PICKUP => 6,
            self::INVOICE => 7,
            self::QUOTE => 8,
            self::SYSTEM_UPDATE => 9,
            self::MARKETING => 10,
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
