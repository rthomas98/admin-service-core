<?php

namespace App\Filament\Resources\Pricings\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PricingForm
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
                            ->columnSpan(3),
                        TextInput::make('equipment_type')
                            ->label('Equipment Type')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('size')
                            ->label('Size')
                            ->columnSpan(3),
                        TextInput::make('category')
                            ->label('Category')
                            ->columnSpan(3),
                        TextInput::make('minimum_rental_days')
                            ->label('Minimum Rental Days')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->columnSpan(4),
                        TextInput::make('maximum_rental_days')
                            ->label('Maximum Rental Days')
                            ->numeric()
                            ->columnSpan(4),
                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->required()
                            ->columnSpan(4),
                    ]),
                Section::make('Rental Rates')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('daily_rate')
                            ->label('Daily Rate')
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(4),
                        TextInput::make('weekly_rate')
                            ->label('Weekly Rate')
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(4),
                        TextInput::make('monthly_rate')
                            ->label('Monthly Rate')
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(4),
                        TextInput::make('delivery_fee')
                            ->label('Delivery Fee')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('pickup_fee')
                            ->label('Pickup Fee')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('cleaning_fee')
                            ->label('Cleaning Fee')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('maintenance_fee')
                            ->label('Maintenance Fee')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('damage_fee')
                            ->label('Damage Fee')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('late_fee_daily')
                            ->label('Late Fee (Daily)')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('emergency_surcharge')
                            ->label('Emergency Surcharge')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('included_services')
                            ->label('Included Services')
                            ->columnSpan(4),
                        TextInput::make('additional_charges')
                            ->label('Additional Charges')
                            ->columnSpan(4),
                        DatePicker::make('effective_from')
                            ->label('Effective From')
                            ->columnSpan(2),
                        DatePicker::make('effective_until')
                            ->label('Effective Until')
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
