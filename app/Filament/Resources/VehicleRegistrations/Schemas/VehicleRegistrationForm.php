<?php

namespace App\Filament\Resources\VehicleRegistrations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class VehicleRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Select::make('vehicle_id')
                    ->relationship('vehicle', 'id')
                    ->required(),
                TextInput::make('registration_number')
                    ->required(),
                TextInput::make('license_plate')
                    ->required(),
                TextInput::make('registration_state')
                    ->required(),
                DatePicker::make('registration_date')
                    ->required(),
                DatePicker::make('expiry_date')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                DatePicker::make('renewal_reminder_date'),
                Toggle::make('auto_renew')
                    ->required(),
                DatePicker::make('last_renewal_date'),
                DatePicker::make('next_renewal_date'),
                TextInput::make('renewal_notice_days')
                    ->required()
                    ->numeric()
                    ->default(30),
                TextInput::make('registration_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('renewal_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('late_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('other_fees')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_paid')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                DatePicker::make('payment_date'),
                TextInput::make('payment_method'),
                TextInput::make('transaction_number'),
                TextInput::make('vin'),
                TextInput::make('make'),
                TextInput::make('model'),
                TextInput::make('year')
                    ->numeric(),
                TextInput::make('color'),
                TextInput::make('weight')
                    ->numeric(),
                TextInput::make('vehicle_class'),
                TextInput::make('fuel_type'),
                TextInput::make('registered_owner'),
                TextInput::make('owner_address'),
                TextInput::make('owner_city'),
                TextInput::make('owner_state'),
                TextInput::make('owner_zip'),
                TextInput::make('insurance_company'),
                TextInput::make('insurance_policy_number'),
                DatePicker::make('insurance_expiry_date'),
                TextInput::make('permits'),
                TextInput::make('endorsements'),
                Toggle::make('dot_compliant')
                    ->required(),
                TextInput::make('dot_number'),
                TextInput::make('mc_number'),
                TextInput::make('registration_document'),
                TextInput::make('insurance_document'),
                TextInput::make('other_documents'),
                TextInput::make('photos'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('renewal_history'),
                TextInput::make('violation_history'),
            ]);
    }
}
