<?php

namespace App\Filament\Resources\FuelLogs\Schemas;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\DriverAssignment;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FuelLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Fuel Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('fuel_date')
                                    ->required()
                                    ->default(now()),
                                
                                Select::make('vehicle_id')
                                    ->label('Vehicle')
                                    ->relationship('vehicle', 'vin')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->year} {$record->make} {$record->model}")
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Select::make('driver_id')
                                    ->label('Driver')
                                    ->relationship('driver', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Select::make('driver_assignment_id')
                                    ->label('Assignment')
                                    ->relationship('driverAssignment', 'id')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->driver->full_name} - {$record->start_date->format('M d, Y')}")
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                
                                Select::make('fuel_type')
                                    ->options([
                                        'regular' => 'Regular',
                                        'diesel' => 'Diesel',
                                        'premium' => 'Premium',
                                        'def' => 'DEF',
                                    ])
                                    ->default('diesel')
                                    ->required(),
                                
                                Toggle::make('is_personal')
                                    ->label('Personal Use')
                                    ->default(false),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Station Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('fuel_station')
                                    ->maxLength(255)
                                    ->nullable(),
                                
                                TextInput::make('location')
                                    ->maxLength(255)
                                    ->nullable(),
                                
                                TextInput::make('odometer_reading')
                                    ->numeric()
                                    ->suffix('miles')
                                    ->required(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Cost Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('gallons')
                                    ->numeric()
                                    ->suffix('gal')
                                    ->step(0.01)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => 
                                        $set('total_cost', $state * $get('price_per_gallon'))
                                    ),
                                
                                TextInput::make('price_per_gallon')
                                    ->numeric()
                                    ->prefix('$')
                                    ->suffix('/gal')
                                    ->step(0.001)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, $set, $get) => 
                                        $set('total_cost', $state * $get('gallons'))
                                    ),
                                
                                TextInput::make('total_cost')
                                    ->numeric()
                                    ->prefix('$')
                                    ->step(0.01)
                                    ->required(),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextInput::make('payment_method')
                                    ->maxLength(255)
                                    ->nullable(),
                                
                                TextInput::make('receipt_number')
                                    ->maxLength(255)
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Documentation')
                    ->schema([
                        FileUpload::make('receipt_image')
                            ->image()
                            ->maxSize(5120)
                            ->directory('fuel-receipts')
                            ->nullable(),
                        
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->nullable(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}