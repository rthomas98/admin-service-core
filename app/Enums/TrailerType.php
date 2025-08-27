<?php

namespace App\Enums;

enum TrailerType: string
{
    case Flatbed = 'flatbed';
    case DryVan = 'dry_van';
    case Refrigerated = 'refrigerated';
    case Tanker = 'tanker';
    case Lowboy = 'lowboy';
    case Dump = 'dump';
    case Container = 'container';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Flatbed => 'Flatbed',
            self::DryVan => 'Dry Van',
            self::Refrigerated => 'Refrigerated',
            self::Tanker => 'Tanker',
            self::Lowboy => 'Lowboy',
            self::Dump => 'Dump',
            self::Container => 'Container',
            self::Other => 'Other',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Flatbed => 'heroicon-o-rectangle-group',
            self::DryVan => 'heroicon-o-cube',
            self::Refrigerated => 'heroicon-o-snowflake',
            self::Tanker => 'heroicon-o-beaker',
            self::Lowboy => 'heroicon-o-minus',
            self::Dump => 'heroicon-o-arrow-up-tray',
            self::Container => 'heroicon-o-square-3-stack-3d',
            self::Other => 'heroicon-o-question-mark-circle',
        };
    }
}
