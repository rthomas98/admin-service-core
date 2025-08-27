<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(3),
                        Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship('invoice', 'id')
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
                        TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(3),
                        TextInput::make('fee_amount')
                            ->label('Fee Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('net_amount')
                            ->label('Net Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('pending')
                            ->columnSpan(3),
                    ]),
                Section::make('Transaction Details')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->columnSpan(3),
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->columnSpan(3),
                        TextInput::make('gateway_transaction_id')
                            ->label('Gateway Transaction ID')
                            ->columnSpan(3),
                        TextInput::make('gateway')
                            ->label('Gateway')
                            ->columnSpan(3),
                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->columnSpan(4),
                        DateTimePicker::make('processed_datetime')
                            ->label('Processed Date & Time')
                            ->columnSpan(4),
                        TextInput::make('gateway_response')
                            ->label('Gateway Response')
                            ->columnSpan(4),
                    ]),
                Section::make('Check Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('check_number')
                            ->label('Check Number')
                            ->columnSpan(4),
                        DatePicker::make('check_date')
                            ->label('Check Date')
                            ->columnSpan(4),
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->columnSpan(4),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
