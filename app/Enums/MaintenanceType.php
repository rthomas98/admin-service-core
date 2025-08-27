<?php

namespace App\Enums;

enum MaintenanceType: string
{
    case Preventive = 'preventive';
    case Repair = 'repair';
    case Inspection = 'inspection';
    case Service = 'service';
    case Tire = 'tire';
    case OilChange = 'oil_change';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Preventive => 'Preventive Maintenance',
            self::Repair => 'Repair',
            self::Inspection => 'Inspection',
            self::Service => 'Service',
            self::Tire => 'Tire Maintenance',
            self::OilChange => 'Oil Change',
            self::Other => 'Other',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Preventive => 'success',
            self::Repair => 'danger',
            self::Inspection => 'primary',
            self::Service => 'info',
            self::Tire => 'warning',
            self::OilChange => 'gray',
            self::Other => 'secondary',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Preventive => 'heroicon-o-shield-check',
            self::Repair => 'heroicon-o-wrench-screwdriver',
            self::Inspection => 'heroicon-o-magnifying-glass',
            self::Service => 'heroicon-o-cog-6-tooth',
            self::Tire => 'heroicon-o-stop-circle',
            self::OilChange => 'heroicon-o-beaker',
            self::Other => 'heroicon-o-question-mark-circle',
        };
    }
}
