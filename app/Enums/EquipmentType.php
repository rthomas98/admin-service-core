<?php

namespace App\Enums;

enum EquipmentType: string
{
    case Forklift = 'forklift';
    case Crane = 'crane';
    case Loader = 'loader';
    case Excavator = 'excavator';
    case Generator = 'generator';
    case Compressor = 'compressor';
    case Tool = 'tool';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Forklift => 'Forklift',
            self::Crane => 'Crane',
            self::Loader => 'Loader',
            self::Excavator => 'Excavator',
            self::Generator => 'Generator',
            self::Compressor => 'Compressor',
            self::Tool => 'Tool',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Forklift => 'heroicon-o-arrow-up-on-square-stack',
            self::Crane => 'heroicon-o-arrow-trending-up',
            self::Loader => 'heroicon-o-cube-transparent',
            self::Excavator => 'heroicon-o-wrench-screwdriver',
            self::Generator => 'heroicon-o-bolt',
            self::Compressor => 'heroicon-o-speaker-wave',
            self::Tool => 'heroicon-o-wrench',
            self::Other => 'heroicon-o-question-mark-circle',
        };
    }
}
