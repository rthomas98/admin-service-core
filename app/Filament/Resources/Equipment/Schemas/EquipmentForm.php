<?php

namespace App\Filament\Resources\Equipment\Schemas;

use App\Enums\EquipmentType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Equipment Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(4),

                        Select::make('type')
                            ->label('Equipment Type')
                            ->options(function () {
                                // Return grouped options for Filament v4
                                $grouped = [];
                                foreach (EquipmentType::cases() as $type) {
                                    $category = $type->category();
                                    if (! isset($grouped[$category])) {
                                        $grouped[$category] = [];
                                    }
                                    $grouped[$category][$type->value] = $type->label();
                                }

                                return $grouped;
                            })
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $type = EquipmentType::from($state);
                                    $set('daily_rate', $type->defaultDailyRate());
                                    $set('delivery_fee', $type->deliveryFee());
                                }
                            })
                            ->columnSpan(4),

                        TextInput::make('unit_number')
                            ->label('Unit Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., RAW-001')
                            ->columnSpan(4),

                        TextInput::make('size')
                            ->label('Size/Capacity')
                            ->placeholder(function (Get $get) {
                                $type = $get('type');
                                if (! $type) {
                                    return 'Select type first';
                                }

                                return match (true) {
                                    str_contains($type, 'dumpster') => 'e.g., 10 cubic yards',
                                    str_contains($type, 'tank') => 'e.g., 250 gallons',
                                    str_contains($type, 'toilet') => 'e.g., Standard',
                                    default => 'Enter size',
                                };
                            })
                            ->columnSpan(3),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Available',
                                'reserved' => 'Reserved',
                                'rented' => 'Rented',
                                'in_transit' => 'In Transit',
                                'maintenance' => 'Maintenance',
                                'damaged' => 'Damaged',
                                'retired' => 'Retired',
                            ])
                            ->required()
                            ->default('available')
                            ->columnSpan(3),

                        Select::make('condition')
                            ->label('Condition')
                            ->options([
                                'excellent' => 'Excellent',
                                'good' => 'Good',
                                'fair' => 'Fair',
                                'poor' => 'Poor',
                            ])
                            ->required()
                            ->default('good')
                            ->columnSpan(3),

                        TextInput::make('color')
                            ->label('Color')
                            ->placeholder('e.g., Blue, Green')
                            ->columnSpan(3),

                        TextInput::make('current_location')
                            ->label('Current Location')
                            ->placeholder('e.g., Warehouse, Customer Site')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('latitude')
                                    ->label('GPS Latitude')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->placeholder('e.g., 18.123456'),

                                TextInput::make('longitude')
                                    ->label('GPS Longitude')
                                    ->numeric()
                                    ->step(0.000001)
                                    ->placeholder('e.g., -77.123456'),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Pricing Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('daily_rate')
                            ->label('Daily Rental Rate')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),

                        TextInput::make('weekly_rate')
                            ->label('Weekly Rental Rate')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),

                        TextInput::make('monthly_rate')
                            ->label('Monthly Rental Rate')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),

                        TextInput::make('delivery_fee')
                            ->label('Delivery Fee')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),

                        TextInput::make('pickup_fee')
                            ->label('Pickup Fee')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),

                        TextInput::make('cleaning_fee')
                            ->label('Cleaning/Service Fee')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),

                        TextInput::make('damage_deposit')
                            ->label('Damage Deposit')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),

                        TextInput::make('purchase_price')
                            ->label('Purchase Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->placeholder('0.00')
                            ->columnSpan(3),
                    ]),

                Section::make('Service & Maintenance')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        DatePicker::make('purchase_date')
                            ->label('Purchase Date')
                            ->columnSpan(3),

                        DatePicker::make('last_service_date')
                            ->label('Last Service Date')
                            ->columnSpan(3),

                        DatePicker::make('next_service_due')
                            ->label('Next Service Due')
                            ->columnSpan(3),

                        Select::make('service_interval')
                            ->label('Service Interval')
                            ->options([
                                'weekly' => 'Weekly',
                                'biweekly' => 'Bi-Weekly',
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'annually' => 'Annually',
                                'as_needed' => 'As Needed',
                            ])
                            ->columnSpan(3),

                        TextInput::make('service_provider')
                            ->label('Service Provider')
                            ->placeholder('e.g., RAW Disposal Maintenance')
                            ->columnSpan(6),

                        TextInput::make('service_contact')
                            ->label('Service Contact')
                            ->placeholder('Phone or Email')
                            ->columnSpan(6),
                    ]),

                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->placeholder('Manufacturer serial number')
                            ->columnSpan(4),

                        TextInput::make('manufacturer')
                            ->label('Manufacturer')
                            ->placeholder('e.g., Waste Management Inc.')
                            ->columnSpan(4),

                        TextInput::make('model')
                            ->label('Model')
                            ->placeholder('e.g., WM-2024')
                            ->columnSpan(4),

                        TextInput::make('year')
                            ->label('Year')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue(date('Y') + 1)
                            ->placeholder(date('Y'))
                            ->columnSpan(3),

                        TextInput::make('weight_capacity')
                            ->label('Weight Capacity')
                            ->placeholder('e.g., 10 tons')
                            ->columnSpan(3),

                        TextInput::make('dimensions')
                            ->label('Dimensions (L x W x H)')
                            ->placeholder('e.g., 20ft x 8ft x 6ft')
                            ->columnSpan(6),

                        ToggleButtons::make('requires_cdl')
                            ->label('Requires CDL to Transport?')
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpan(6),

                        ToggleButtons::make('has_gps_tracker')
                            ->label('Has GPS Tracker?')
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpan(6),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->placeholder('Any additional information about this equipment...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
