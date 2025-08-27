<?php

namespace App\Filament\Resources\Quotes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Quote Information')
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
                        TextInput::make('quote_number')
                            ->label('Quote Number')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->columnSpan(3),
                        DatePicker::make('quote_date')
                            ->label('Quote Date')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('valid_until')
                            ->label('Valid Until')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('accepted_date')
                            ->label('Accepted Date')
                            ->columnSpan(2),
                        DatePicker::make('requested_delivery_date')
                            ->label('Requested Delivery Date')
                            ->columnSpan(2),
                        DatePicker::make('requested_pickup_date')
                            ->label('Requested Pickup Date')
                            ->columnSpan(2),
                    ]),
                Section::make('Financial Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('items')
                            ->label('Items')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('discount_amount')
                            ->label('Discount Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('tax_rate')
                            ->label('Tax Rate (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->default(0)
                            ->columnSpan(6),
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(6),
                    ]),
                Section::make('Delivery Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
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
                        Select::make('converted_service_order_id')
                            ->label('Converted Service Order')
                            ->relationship('convertedServiceOrder', 'id')
                            ->columnSpanFull(),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('terms_conditions')
                            ->label('Terms & Conditions')
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
