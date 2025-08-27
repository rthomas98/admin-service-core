<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Information')
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
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('sent_date')
                            ->label('Sent Date')
                            ->columnSpan(3),
                        DatePicker::make('paid_date')
                            ->label('Paid Date')
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->columnSpan(6),
                        Toggle::make('is_recurring')
                            ->label('Is Recurring')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('recurring_frequency')
                            ->label('Recurring Frequency')
                            ->columnSpan(3),
                    ]),
                Section::make('Financial Details')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
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
                            ->columnSpan(4),
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
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
                        TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('balance_due')
                            ->label('Balance Due')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                    ]),
                Section::make('Billing Address')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('billing_address')
                            ->label('Billing Address')
                            ->columnSpanFull(),
                        TextInput::make('billing_city')
                            ->label('City')
                            ->columnSpan(4),
                        TextInput::make('billing_parish')
                            ->label('Parish')
                            ->columnSpan(4),
                        TextInput::make('billing_postal_code')
                            ->label('Postal Code')
                            ->columnSpan(4),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('line_items')
                            ->label('Line Items')
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpan(6),
                        Textarea::make('terms_conditions')
                            ->label('Terms & Conditions')
                            ->rows(4)
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
