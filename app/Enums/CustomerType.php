<?php

namespace App\Enums;

enum CustomerType: string
{
    case RESIDENTIAL = 'residential';
    case COMMERCIAL = 'commercial';
    case INDUSTRIAL = 'industrial';

    public function label(): string
    {
        return match($this) {
            self::RESIDENTIAL => 'Residential',
            self::COMMERCIAL => 'Commercial',
            self::INDUSTRIAL => 'Industrial',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::RESIDENTIAL => 'info',
            self::COMMERCIAL => 'warning',
            self::INDUSTRIAL => 'primary',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::RESIDENTIAL => 'heroicon-o-home',
            self::COMMERCIAL => 'heroicon-o-building-office',
            self::INDUSTRIAL => 'heroicon-o-building-office-2',
        };
    }
}