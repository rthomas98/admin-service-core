<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum InvoiceStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Viewed = 'viewed';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case WrittenOff = 'written_off';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Viewed => 'Viewed',
            self::PartiallyPaid => 'Partially Paid',
            self::Paid => 'Paid',
            self::Overdue => 'Overdue',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
            self::WrittenOff => 'Written Off',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'info',
            self::Viewed => 'info',
            self::PartiallyPaid => 'warning',
            self::Paid => 'success',
            self::Overdue => 'danger',
            self::Cancelled => 'gray',
            self::Refunded => 'warning',
            self::WrittenOff => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Sent => 'heroicon-o-paper-airplane',
            self::Viewed => 'heroicon-o-eye',
            self::PartiallyPaid => 'heroicon-o-currency-dollar',
            self::Paid => 'heroicon-o-check-circle',
            self::Overdue => 'heroicon-o-clock',
            self::Cancelled => 'heroicon-o-x-circle',
            self::Refunded => 'heroicon-o-arrow-uturn-left',
            self::WrittenOff => 'heroicon-o-x-mark',
        };
    }

    public function canBePaid(): bool
    {
        return in_array($this, [
            self::Sent,
            self::Viewed,
            self::PartiallyPaid,
            self::Overdue,
        ]);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [
            self::Draft,
            self::Sent,
            self::Viewed,
            self::Overdue,
        ]);
    }

    public function canBeSent(): bool
    {
        return $this === self::Draft;
    }

    public function isActive(): bool
    {
        return ! in_array($this, [
            self::Paid,
            self::Cancelled,
            self::Refunded,
            self::WrittenOff,
        ]);
    }
}
