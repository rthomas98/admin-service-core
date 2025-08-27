<?php

namespace App\Filament\Resources\ServiceOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Order Information')
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
                        TextInput::make('order_number')
                            ->label('Order Number')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('service_type')
                            ->label('Service Type')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('pending')
                            ->columnSpan(6),
                        TextInput::make('equipment_requested')
                            ->label('Equipment Requested')
                            ->columnSpan(6),
                        DatePicker::make('delivery_date')
                            ->label('Delivery Date')
                            ->columnSpan(4),
                        TimePicker::make('delivery_time_start')
                            ->label('Delivery Start Time')
                            ->columnSpan(4),
                        TimePicker::make('delivery_time_end')
                            ->label('Delivery End Time')
                            ->columnSpan(4),
                        TextInput::make('delivery_address')
                            ->label('Delivery Address')
                            ->columnSpanFull(),
                        TextInput::make('delivery_city')
                            ->label('City')
                            ->columnSpan(4),
                        TextInput::make('delivery_parish')
                            ->label('Parish')
                            ->columnSpan(4),
                        TextInput::make('delivery_postal_code')
                            ->label('Postal Code')
                            ->columnSpan(4),
                    ]),
                Section::make('Pickup Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        DatePicker::make('pickup_date')
                            ->label('Pickup Date')
                            ->columnSpan(4),
                        TimePicker::make('pickup_time_start')
                            ->label('Pickup Start Time')
                            ->columnSpan(4),
                        TimePicker::make('pickup_time_end')
                            ->label('Pickup End Time')
                            ->columnSpan(4),
                        TextInput::make('pickup_address')
                            ->label('Pickup Address')
                            ->columnSpanFull(),
                        TextInput::make('pickup_city')
                            ->label('City')
                            ->columnSpan(4),
                        TextInput::make('pickup_parish')
                            ->label('Parish')
                            ->columnSpan(4),
                        TextInput::make('pickup_postal_code')
                            ->label('Postal Code')
                            ->columnSpan(4),
                    ]),
                Section::make('Financial Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('discount_amount')
                            ->label('Discount Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('final_amount')
                            ->label('Final Amount')
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
                        Textarea::make('special_instructions')
                            ->label('Special Instructions')
                            ->rows(4)
                            ->columnSpan(6),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
