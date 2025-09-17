<?php

namespace App\Filament\Resources\WasteCollections\Schemas;

use App\Models\Driver;
use App\Models\Vehicle;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WasteCollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Collection Information')
                    ->description('Basic information about the waste collection')
                    ->schema([
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required(),
                        Select::make('customer_id')
                            ->relationship('customer', 'organization')
                            ->searchable()
                            ->preload(),
                        Select::make('route_id')
                            ->relationship('route', 'name')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3),

                Section::make('Assignment Details')
                    ->description('Driver and vehicle assignment')
                    ->schema([
                        Select::make('driver_id')
                            ->relationship('driver')
                            ->getOptionLabelFromRecordUsing(fn (Driver $record) => "{$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload(),
                        Select::make('truck_id')
                            ->relationship('truck')
                            ->getOptionLabelFromRecordUsing(fn (Vehicle $record) => "{$record->make} {$record->model} - {$record->license_plate}")
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Schedule')
                    ->description('Collection schedule and timing')
                    ->schema([
                        DatePicker::make('scheduled_date')
                            ->required(),
                        TimePicker::make('scheduled_time'),
                        Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('scheduled'),
                        DateTimePicker::make('completed_at'),
                    ])
                    ->columns(2),

                Section::make('Waste Details')
                    ->description('Information about the waste being collected')
                    ->schema([
                        TextInput::make('waste_type')
                            ->required()
                            ->default('general'),
                        TextInput::make('estimated_weight')
                            ->numeric()
                            ->suffix('lbs'),
                        TextInput::make('actual_weight')
                            ->numeric()
                            ->suffix('lbs'),
                    ])
                    ->columns(3),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }
}
