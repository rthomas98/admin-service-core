<?php

namespace App\Filament\Resources\EmergencyServices\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmergencyServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Emergency Request Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(3),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('emergency_number')
                            ->label('Emergency Number')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('pending')
                            ->columnSpan(3),
                        DateTimePicker::make('request_datetime')
                            ->label('Request Date & Time')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('urgency_level')
                            ->label('Urgency Level')
                            ->required()
                            ->default('medium')
                            ->columnSpan(3),
                        TextInput::make('emergency_type')
                            ->label('Emergency Type')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('equipment_needed')
                            ->label('Equipment Needed')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('contact_name')
                            ->label('Contact Name')
                            ->required()
                            ->columnSpan(6),
                        TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->required()
                            ->columnSpan(6),
                    ]),
                Section::make('Location Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('location_address')
                            ->label('Address')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('location_city')
                            ->label('City')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('location_parish')
                            ->label('Parish')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('location_postal_code')
                            ->label('Postal Code')
                            ->columnSpan(4),
                        TextInput::make('location_latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->columnSpan(6),
                        TextInput::make('location_longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->columnSpan(6),
                    ]),
                Section::make('Service Timeline')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('target_response_minutes')
                            ->label('Target Response (Minutes)')
                            ->required()
                            ->numeric()
                            ->default(60)
                            ->columnSpan(6),
                        TextInput::make('actual_response_minutes')
                            ->label('Actual Response (Minutes)')
                            ->numeric()
                            ->columnSpan(6),
                        DateTimePicker::make('required_by_datetime')
                            ->label('Required By')
                            ->columnSpan(6),
                        DateTimePicker::make('assigned_datetime')
                            ->label('Assigned Date & Time')
                            ->columnSpan(6),
                        DateTimePicker::make('dispatched_datetime')
                            ->label('Dispatched Date & Time')
                            ->columnSpan(4),
                        DateTimePicker::make('arrival_datetime')
                            ->label('Arrival Date & Time')
                            ->columnSpan(4),
                        DateTimePicker::make('completion_datetime')
                            ->label('Completion Date & Time')
                            ->columnSpan(4),
                    ]),
                Section::make('Assignment & Cost')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('assigned_driver_id')
                            ->label('Assigned Driver')
                            ->relationship('assignedDriver', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->columnSpan(6),
                        Select::make('assigned_technician_id')
                            ->label('Assigned Technician')
                            ->relationship('assignedTechnician', 'name')
                            ->columnSpan(6),
                        TextInput::make('emergency_surcharge')
                            ->label('Emergency Surcharge')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(6),
                        TextInput::make('total_cost')
                            ->label('Total Cost')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(6),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('special_instructions')
                            ->label('Special Instructions')
                            ->rows(3)
                            ->columnSpan(6),
                        Textarea::make('completion_notes')
                            ->label('Completion Notes')
                            ->rows(3)
                            ->columnSpan(6),
                        TextInput::make('photos')
                            ->label('Photos')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
