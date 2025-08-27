<?php

namespace App\Enums;

enum FuelType: string
{
    case Diesel = 'diesel';
    case Gasoline = 'gasoline';
    case Electric = 'electric';
    case Hybrid = 'hybrid';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Diesel => 'Diesel',
            self::Gasoline => 'Gasoline',
            self::Electric => 'Electric',
            self::Hybrid => 'Hybrid',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Diesel => 'heroicon-o-fire',
            self::Gasoline => 'heroicon-o-fire',
            self::Electric => 'heroicon-o-bolt',
            self::Hybrid => 'heroicon-o-battery-50',
            self::Other => 'heroicon-o-question-mark-circle',
        };
    }
}
