<?php

namespace App\Filament\Resources\Vehicles\Schemas;

use App\Enums\FuelType;
use App\Enums\VehicleStatus;
use App\Enums\VehicleType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vehicle Information')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('unit_number')
                                    ->label('Unit Number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g., T-001'),
                                TextInput::make('vin')
                                    ->label('VIN')
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(17)
                                    ->placeholder('17-character VIN'),
                            ]),
                        Grid::make(4)
                            ->components([
                                TextInput::make('year')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1900)
                                    ->maxValue(date('Y') + 1)
                                    ->default(date('Y')),
                                TextInput::make('make')
                                    ->required()
                                    ->placeholder('e.g., Freightliner'),
                                TextInput::make('model')
                                    ->required()
                                    ->placeholder('e.g., Cascadia'),
                                TextInput::make('color')
                                    ->placeholder('e.g., White'),
                            ]),
                        Grid::make(2)
                            ->components([
                                Select::make('type')
                                    ->options(VehicleType::class)
                                    ->default(VehicleType::Truck)
                                    ->required()
                                    ->searchable(),
                                Select::make('fuel_type')
                                    ->options(FuelType::class)
                                    ->default(FuelType::Diesel)
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])->columnSpanFull(),
                
                Section::make('Registration & Status')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextInput::make('license_plate')
                                    ->label('License Plate')
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g., ABC-1234'),
                                TextInput::make('registration_state')
                                    ->label('Registration State')
                                    ->required()
                                    ->default('LA')
                                    ->maxLength(2)
                                    ->placeholder('e.g., LA'),
                                DatePicker::make('registration_expiry')
                                    ->label('Registration Expiry')
                                    ->minDate(now()),
                            ]),
                        Grid::make(3)
                            ->components([
                                Select::make('status')
                                    ->options(VehicleStatus::class)
                                    ->default(VehicleStatus::Active)
                                    ->required()
                                    ->searchable(),
                                TextInput::make('odometer')
                                    ->label('Current Odometer')
                                    ->numeric()
                                    ->suffix('miles')
                                    ->placeholder('e.g., 50000'),
                                DatePicker::make('odometer_date')
                                    ->label('Odometer Reading Date')
                                    ->maxDate(now())
                                    ->default(now()),
                            ]),
                        TextInput::make('fuel_capacity')
                            ->label('Fuel Capacity')
                            ->numeric()
                            ->suffix('gallons')
                            ->placeholder('e.g., 150'),
                    ])->columnSpanFull(),
                
                Section::make('Purchase & Finance')
                    ->components([
                        Grid::make(3)
                            ->components([
                                DatePicker::make('purchase_date')
                                    ->label('Purchase Date')
                                    ->maxDate(now()),
                                TextInput::make('purchase_price')
                                    ->label('Purchase Price')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('e.g., 75000'),
                                TextInput::make('purchase_vendor')
                                    ->label('Purchase Vendor')
                                    ->placeholder('e.g., Truck Dealership Inc.'),
                            ]),
                        Grid::make(2)
                            ->components([
                                Toggle::make('is_leased')
                                    ->label('Is this vehicle leased?')
                                    ->reactive()
                                    ->default(false),
                                DatePicker::make('lease_end_date')
                                    ->label('Lease End Date')
                                    ->minDate(now())
                                    ->visible(fn ($get) => $get('is_leased'))
                                    ->required(fn ($get) => $get('is_leased')),
                            ]),
                    ])->columnSpanFull(),
                
                Section::make('Additional Information')
                    ->components([
                        FileUpload::make('image_path')
                            ->label('Vehicle Photo')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('250')
                            ->maxSize(5120)
                            ->directory('vehicles')
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        KeyValue::make('specifications')
                            ->label('Additional Specifications')
                            ->keyLabel('Specification')
                            ->valueLabel('Value')
                            ->addButtonLabel('Add Specification')
                            ->columnSpanFull(),
                    ])->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}