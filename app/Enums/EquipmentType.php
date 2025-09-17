<?php

namespace App\Enums;

enum EquipmentType: string
{
    // Waste Containers
    case Dumpster10YD = 'dumpster_10yd';
    case Dumpster20YD = 'dumpster_20yd';
    case Dumpster30YD = 'dumpster_30yd';
    case Dumpster40YD = 'dumpster_40yd';
    case RollOffContainer = 'roll_off_container';

    // Portable Sanitation
    case PortableToiletStandard = 'portable_toilet_standard';
    case PortableToiletDeluxe = 'portable_toilet_deluxe';
    case PortableToiletADA = 'portable_toilet_ada';
    case PortableToiletTrailer = 'portable_toilet_trailer';

    // Handwash Stations
    case HandwashStation2 = 'handwash_station_2';
    case HandwashStation4 = 'handwash_station_4';
    case HandwashStationDeluxe = 'handwash_station_deluxe';

    // Tanks
    case HoldingTank250 = 'holding_tank_250';
    case HoldingTank500 = 'holding_tank_500';
    case HoldingTank1000 = 'holding_tank_1000';
    case WaterTank250 = 'water_tank_250';
    case WaterTank500 = 'water_tank_500';
    case WaterTank1000 = 'water_tank_1000';

    // Other Equipment
    case FencingPanel = 'fencing_panel';
    case LightTower = 'light_tower';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            // Waste Containers
            self::Dumpster10YD => '10 Yard Dumpster',
            self::Dumpster20YD => '20 Yard Dumpster',
            self::Dumpster30YD => '30 Yard Dumpster',
            self::Dumpster40YD => '40 Yard Dumpster',
            self::RollOffContainer => 'Roll-Off Container',

            // Portable Sanitation
            self::PortableToiletStandard => 'Standard Portable Toilet',
            self::PortableToiletDeluxe => 'Deluxe Portable Toilet',
            self::PortableToiletADA => 'ADA Compliant Portable Toilet',
            self::PortableToiletTrailer => 'Portable Toilet Trailer',

            // Handwash Stations
            self::HandwashStation2 => '2-Station Handwash Unit',
            self::HandwashStation4 => '4-Station Handwash Unit',
            self::HandwashStationDeluxe => 'Deluxe Handwash Station',

            // Tanks
            self::HoldingTank250 => '250 Gallon Holding Tank',
            self::HoldingTank500 => '500 Gallon Holding Tank',
            self::HoldingTank1000 => '1000 Gallon Holding Tank',
            self::WaterTank250 => '250 Gallon Water Tank',
            self::WaterTank500 => '500 Gallon Water Tank',
            self::WaterTank1000 => '1000 Gallon Water Tank',

            // Other
            self::FencingPanel => 'Temporary Fencing Panel',
            self::LightTower => 'Light Tower',
            self::Other => 'Other Equipment',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            // Waste Containers
            self::Dumpster10YD, self::Dumpster20YD,
            self::Dumpster30YD, self::Dumpster40YD,
            self::RollOffContainer => 'heroicon-o-cube',

            // Portable Sanitation
            self::PortableToiletStandard, self::PortableToiletDeluxe,
            self::PortableToiletADA, self::PortableToiletTrailer => 'heroicon-o-home',

            // Handwash Stations
            self::HandwashStation2, self::HandwashStation4,
            self::HandwashStationDeluxe => 'heroicon-o-hand-raised',

            // Tanks
            self::HoldingTank250, self::HoldingTank500, self::HoldingTank1000 => 'heroicon-o-cylinder',
            self::WaterTank250, self::WaterTank500, self::WaterTank1000 => 'heroicon-o-beaker',

            // Other
            self::FencingPanel => 'heroicon-o-view-columns',
            self::LightTower => 'heroicon-o-light-bulb',
            self::Other => 'heroicon-o-question-mark-circle',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::Dumpster10YD, self::Dumpster20YD,
            self::Dumpster30YD, self::Dumpster40YD,
            self::RollOffContainer => 'Waste Containers',

            self::PortableToiletStandard, self::PortableToiletDeluxe,
            self::PortableToiletADA, self::PortableToiletTrailer => 'Portable Sanitation',

            self::HandwashStation2, self::HandwashStation4,
            self::HandwashStationDeluxe => 'Handwash Stations',

            self::HoldingTank250, self::HoldingTank500, self::HoldingTank1000,
            self::WaterTank250, self::WaterTank500, self::WaterTank1000 => 'Tanks',

            self::FencingPanel, self::LightTower, self::Other => 'Other Equipment',
        };
    }

    public function defaultDailyRate(): float
    {
        return match ($this) {
            // Waste Containers - pricing per day
            self::Dumpster10YD => 75.00,
            self::Dumpster20YD => 95.00,
            self::Dumpster30YD => 115.00,
            self::Dumpster40YD => 135.00,
            self::RollOffContainer => 150.00,

            // Portable Sanitation - pricing per week (converted to daily)
            self::PortableToiletStandard => 15.00,
            self::PortableToiletDeluxe => 25.00,
            self::PortableToiletADA => 35.00,
            self::PortableToiletTrailer => 75.00,

            // Handwash Stations
            self::HandwashStation2 => 10.00,
            self::HandwashStation4 => 15.00,
            self::HandwashStationDeluxe => 25.00,

            // Tanks
            self::HoldingTank250 => 50.00,
            self::HoldingTank500 => 75.00,
            self::HoldingTank1000 => 100.00,
            self::WaterTank250 => 40.00,
            self::WaterTank500 => 60.00,
            self::WaterTank1000 => 80.00,

            // Other
            self::FencingPanel => 5.00,
            self::LightTower => 100.00,
            self::Other => 50.00,
        };
    }

    public function deliveryFee(): float
    {
        return match ($this) {
            // Larger items have higher delivery fees
            self::Dumpster10YD => 75.00,
            self::Dumpster20YD => 85.00,
            self::Dumpster30YD => 95.00,
            self::Dumpster40YD => 110.00,
            self::RollOffContainer => 125.00,

            self::PortableToiletStandard, self::PortableToiletDeluxe => 35.00,
            self::PortableToiletADA => 50.00,
            self::PortableToiletTrailer => 75.00,

            self::HandwashStation2, self::HandwashStation4 => 25.00,
            self::HandwashStationDeluxe => 35.00,

            self::HoldingTank250, self::WaterTank250 => 65.00,
            self::HoldingTank500, self::WaterTank500 => 85.00,
            self::HoldingTank1000, self::WaterTank1000 => 110.00,

            self::FencingPanel => 15.00,
            self::LightTower => 75.00,
            self::Other => 50.00,
        };
    }

    public static function forCategory(string $category): array
    {
        return array_filter(self::cases(), fn ($case) => $case->category() === $category);
    }
}
