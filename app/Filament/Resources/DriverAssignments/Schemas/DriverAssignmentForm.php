<?php

namespace App\Filament\Resources\DriverAssignments\Schemas;

use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\Trailer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DriverAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Assignment Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('driver_id')
                                    ->label('Driver')
                                    ->relationship('driver', 'first_name')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Select::make('vehicle_id')
                                    ->label('Vehicle')
                                    ->relationship('vehicle', 'vin')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->year} {$record->make} {$record->model}")
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                
                                Select::make('trailer_id')
                                    ->label('Trailer')
                                    ->relationship('trailer', 'vin')
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->year} {$record->make} {$record->model}")
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                
                                Select::make('status')
                                    ->options([
                                        'scheduled' => 'Scheduled',
                                        'active' => 'Active',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('scheduled')
                                    ->required(),
                                
                                DateTimePicker::make('start_date')
                                    ->required(),
                                
                                DateTimePicker::make('end_date')
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Route Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('route')
                                    ->maxLength(255)
                                    ->nullable(),
                                
                                TextInput::make('origin')
                                    ->maxLength(255)
                                    ->nullable(),
                                
                                TextInput::make('destination')
                                    ->maxLength(255)
                                    ->nullable(),
                                
                                TextInput::make('expected_duration_hours')
                                    ->numeric()
                                    ->suffix('hours')
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Cargo Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('cargo_type')
                                    ->maxLength(255)
                                    ->nullable(),
                                
                                TextInput::make('cargo_weight')
                                    ->numeric()
                                    ->suffix('lbs')
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Trip Metrics')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('mileage_start')
                                    ->numeric()
                                    ->suffix('miles')
                                    ->nullable(),
                                
                                TextInput::make('mileage_end')
                                    ->numeric()
                                    ->suffix('miles')
                                    ->nullable(),
                                
                                TextInput::make('fuel_used')
                                    ->numeric()
                                    ->suffix('gallons')
                                    ->nullable(),
                                
                                TextInput::make('actual_duration_hours')
                                    ->numeric()
                                    ->suffix('hours')
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Notes')
                    ->schema([
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