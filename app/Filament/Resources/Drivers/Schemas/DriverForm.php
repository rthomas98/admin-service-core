<?php

namespace App\Filament\Resources\Drivers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Driver Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(3),
                        Select::make('user_id')
                            ->label('User Account')
                            ->relationship('user', 'name')
                            ->columnSpan(3),
                        TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->columnSpan(4),
                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->columnSpan(4),
                        DatePicker::make('hired_date')
                            ->label('Hired Date')
                            ->columnSpan(4),
                        TextInput::make('license_number')
                            ->label('License Number')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('license_class')
                            ->label('License Class')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('license_expiry_date')
                            ->label('License Expiry Date')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('active')
                            ->columnSpan(3),
                    ]),
                Section::make('Vehicle Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('vehicle_type')
                            ->label('Vehicle Type')
                            ->columnSpan(3),
                        TextInput::make('vehicle_registration')
                            ->label('Vehicle Registration')
                            ->columnSpan(3),
                        TextInput::make('vehicle_make')
                            ->label('Vehicle Make')
                            ->columnSpan(2),
                        TextInput::make('vehicle_model')
                            ->label('Vehicle Model')
                            ->columnSpan(2),
                        TextInput::make('vehicle_year')
                            ->label('Vehicle Year')
                            ->numeric()
                            ->columnSpan(2),
                        Toggle::make('can_lift_heavy')
                            ->label('Can Lift Heavy Items')
                            ->required()
                            ->columnSpan(3),
                        Toggle::make('has_truck_crane')
                            ->label('Has Truck Crane')
                            ->required()
                            ->columnSpan(3),
                    ]),
                Section::make('Work Schedule & Areas')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TimePicker::make('shift_start_time')
                            ->label('Shift Start Time')
                            ->columnSpan(3),
                        TimePicker::make('shift_end_time')
                            ->label('Shift End Time')
                            ->columnSpan(3),
                        TextInput::make('available_days')
                            ->label('Available Days')
                            ->columnSpan(3),
                        TextInput::make('hourly_rate')
                            ->label('Hourly Rate')
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(3),
                        TextInput::make('service_areas')
                            ->label('Service Areas')
                            ->columnSpan(12),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->columnSpan(12)
                            ->rows(4),
                    ]),
            ]);
    }
}