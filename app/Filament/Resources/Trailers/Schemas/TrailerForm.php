<?php

namespace App\Filament\Resources\Trailers\Schemas;

use App\Enums\TrailerType;
use App\Enums\VehicleStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrailerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Trailer Information')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('unit_number')
                                    ->label('Unit Number')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('e.g., TR-001'),
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
                                    ->placeholder('e.g., Great Dane'),
                                TextInput::make('model')
                                    ->placeholder('e.g., Everest'),
                                Select::make('type')
                                    ->options(TrailerType::class)
                                    ->default(TrailerType::Flatbed)
                                    ->required()
                                    ->searchable(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Dimensions & Capacity')
                    ->components([
                        Grid::make(3)
                            ->components([
                                TextInput::make('length')
                                    ->label('Length')
                                    ->numeric()
                                    ->suffix('feet')
                                    ->placeholder('e.g., 53'),
                                TextInput::make('width')
                                    ->label('Width')
                                    ->numeric()
                                    ->suffix('feet')
                                    ->placeholder('e.g., 8.5'),
                                TextInput::make('height')
                                    ->label('Height')
                                    ->numeric()
                                    ->suffix('feet')
                                    ->placeholder('e.g., 13.5'),
                            ]),
                        Grid::make(2)
                            ->components([
                                TextInput::make('capacity_weight')
                                    ->label('Weight Capacity')
                                    ->numeric()
                                    ->suffix('lbs')
                                    ->placeholder('e.g., 45000'),
                                TextInput::make('capacity_volume')
                                    ->label('Volume Capacity')
                                    ->numeric()
                                    ->suffix('cubic feet')
                                    ->placeholder('e.g., 3489'),
                            ]),
                    ])
                    ->columnSpanFull(),

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
                                DatePicker::make('last_inspection_date')
                                    ->label('Last Inspection Date')
                                    ->maxDate(now()),
                                DatePicker::make('next_inspection_date')
                                    ->label('Next Inspection Date')
                                    ->minDate(now()),
                            ]),
                    ])
                    ->columnSpanFull(),

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
                                    ->placeholder('e.g., 35000'),
                                TextInput::make('purchase_vendor')
                                    ->label('Purchase Vendor')
                                    ->placeholder('e.g., Trailer Sales Inc.'),
                            ]),
                        Grid::make(2)
                            ->components([
                                Toggle::make('is_leased')
                                    ->label('Is this trailer leased?')
                                    ->reactive()
                                    ->default(false),
                                DatePicker::make('lease_end_date')
                                    ->label('Lease End Date')
                                    ->minDate(now())
                                    ->visible(fn ($get) => $get('is_leased'))
                                    ->required(fn ($get) => $get('is_leased')),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Additional Information')
                    ->components([
                        FileUpload::make('image_path')
                            ->label('Trailer Photo')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('250')
                            ->maxSize(5120)
                            ->directory('trailers')
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
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}