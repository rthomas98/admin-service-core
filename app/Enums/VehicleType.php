<?php

namespace App\Enums;

enum VehicleType: string
{
    case Truck = 'truck';
    case Van = 'van';
    case Pickup = 'pickup';
    case SUV = 'suv';
    case Car = 'car';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Truck => 'Truck',
            self::Van => 'Van',
            self::Pickup => 'Pickup',
            self::SUV => 'SUV',
            self::Car => 'Car',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Truck => 'heroicon-o-truck',
            self::Van => 'heroicon-o-cube-transparent',
            self::Pickup => 'heroicon-o-cube',
            self::SUV => 'heroicon-o-square-3-stack-3d',
            self::Car => 'heroicon-o-rectangle-stack',
            self::Other => 'heroicon-o-question-mark-circle',
        };
    }
}
