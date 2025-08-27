<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case OutOfService = 'out_of_service';
    case Sold = 'sold';
    case Retired = 'retired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Maintenance => 'In Maintenance',
            self::OutOfService => 'Out of Service',
            self::Sold => 'Sold',
            self::Retired => 'Retired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Maintenance => 'warning',
            self::OutOfService => 'danger',
            self::Sold => 'gray',
            self::Retired => 'gray',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Maintenance => 'heroicon-o-wrench-screwdriver',
            self::OutOfService => 'heroicon-o-x-circle',
            self::Sold => 'heroicon-o-currency-dollar',
            self::Retired => 'heroicon-o-archive-box',
        };
    }
}
