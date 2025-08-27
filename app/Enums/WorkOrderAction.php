<?php

namespace App\Enums;

enum WorkOrderAction: string
{
    case DELIVERY = 'Delivery';
    case PICKUP = 'Pickup';
    case SERVICE = 'Service';
    case EMERGENCY = 'Emergency';
    case OTHER = 'Other';

    public function label(): string
    {
        return $this->value;
    }

    public function icon(): string
    {
        return match($this) {
            self::DELIVERY => 'heroicon-o-truck',
            self::PICKUP => 'heroicon-o-arrow-up-circle',
            self::SERVICE => 'heroicon-o-wrench-screwdriver',
            self::EMERGENCY => 'heroicon-o-exclamation-triangle',
            self::OTHER => 'heroicon-o-document-text',
        };
    }
}