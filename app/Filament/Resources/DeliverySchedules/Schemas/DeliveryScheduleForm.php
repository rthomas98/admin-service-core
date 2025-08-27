<?php

namespace App\Filament\Resources\DeliverySchedules\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class DeliveryScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Schedule Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(3),
                        Select::make('service_order_id')
                            ->label('Service Order')
                            ->relationship('serviceOrder', 'id')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('type')
                            ->label('Type')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('scheduled')
                            ->columnSpan(3),
                        Select::make('equipment_id')
                            ->label('Equipment')
                            ->relationship('equipment', 'id')
                            ->columnSpan(3),
                        Select::make('driver_id')
                            ->label('Driver')
                            ->options(function () {
                                return \App\Models\Driver::query()
                                    ->where('company_id', \Filament\Facades\Filament::getTenant()->id)
                                    ->orderBy('first_name')
                                    ->orderBy('last_name')
                                    ->get()
                                    ->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->columnSpan(3),
                        DateTimePicker::make('scheduled_datetime')
                            ->label('Scheduled Date/Time')
                            ->required()
                            ->columnSpan(3),
                        DateTimePicker::make('actual_datetime')
                            ->label('Actual Date/Time')
                            ->columnSpan(3),
                    ]),
                Section::make('Delivery Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('delivery_address')
                            ->label('Delivery Address')
                            ->required()
                            ->columnSpan(6),
                        TextInput::make('delivery_city')
                            ->label('Delivery City')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('delivery_parish')
                            ->label('Delivery Parish')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('delivery_postal_code')
                            ->label('Delivery Postal Code')
                            ->columnSpan(3),
                        TextInput::make('delivery_latitude')
                            ->label('Delivery Latitude')
                            ->numeric()
                            ->columnSpan(3),
                        TextInput::make('delivery_longitude')
                            ->label('Delivery Longitude')
                            ->numeric()
                            ->columnSpan(3),
                        TextInput::make('estimated_duration_minutes')
                            ->label('Estimated Duration (minutes)')
                            ->numeric()
                            ->columnSpan(3),
                        TextInput::make('actual_duration_minutes')
                            ->label('Actual Duration (minutes)')
                            ->numeric()
                            ->columnSpan(4),
                        TextInput::make('travel_distance_km')
                            ->label('Travel Distance (km)')
                            ->numeric()
                            ->columnSpan(4),
                    ]),
                Section::make('Completion Details')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('delivery_instructions')
                            ->label('Delivery Instructions')
                            ->columnSpan(6),
                        Textarea::make('completion_notes')
                            ->label('Completion Notes')
                            ->columnSpan(6),
                        TextInput::make('photos')
                            ->label('Photos')
                            ->columnSpan(6),
                        TextInput::make('signature')
                            ->label('Signature')
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
