<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Information')
                    ->description('Basic invoice details and customer information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(4),
                            
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $customer = \App\Models\Customer::find($state);
                                    if ($customer) {
                                        $set('billing_address', $customer->address);
                                        $set('billing_city', $customer->city);
                                        $set('billing_parish', $customer->state);
                                        $set('billing_postal_code', $customer->zip);
                                    }
                                }
                            })
                            ->columnSpan(4),
                            
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->default(fn () => 'INV-' . date('Y') . '-' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT))
                            ->columnSpan(4),
                            
                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->default(now())
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('due_date', \Carbon\Carbon::parse($state)->addDays(30));
                                }
                            })
                            ->columnSpan(3),
                            
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now()->addDays(30))
                            ->columnSpan(3),
                            
                        Select::make('status')
                            ->label('Invoice Status')
                            ->options([
                                'draft' => 'Draft - Not sent',
                                'sent' => 'Sent - Awaiting payment',
                                'partially_paid' => 'Partially Paid',
                                'paid' => 'Paid - Complete',
                                'cancelled' => 'Cancelled',
                                'refunded' => 'Refunded',
                            ])
                            ->required()
                            ->default('draft')
                            ->columnSpan(3)
                            ->helperText('Select the current status of this invoice'),
                            
                        Select::make('service_order_id')
                            ->label('Service Order (Optional)')
                            ->relationship('serviceOrder', 'id')
                            ->searchable()
                            ->columnSpan(3),
                    ]),
                    
                Section::make('Equipment & Services')
                    ->description('Add equipment rentals, services, or products to this invoice')
                    ->columnSpanFull()
                    ->components([
                        Repeater::make('line_items')
                            ->label('Line Items')
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Select::make('type')
                                            ->label('Type')
                                            ->options([
                                                'equipment' => 'Equipment Rental',
                                                'service' => 'Service',
                                                'product' => 'Product',
                                                'disposal' => 'Disposal Fee',
                                                'delivery' => 'Delivery',
                                                'other' => 'Other',
                                            ])
                                            ->required()
                                            ->reactive()
                                            ->columnSpan(3),
                                            
                                        TextInput::make('description')
                                            ->label('Description')
                                            ->required()
                                            ->placeholder('e.g., 20-yard dumpster rental')
                                            ->columnSpan(5),
                                            
                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->reactive()
                                            ->minValue(1)
                                            ->columnSpan(2),
                                            
                                        TextInput::make('unit_price')
                                            ->label('Unit Price')
                                            ->numeric()
                                            ->required()
                                            ->prefix('$')
                                            ->reactive()
                                            ->columnSpan(2),
                                            
                                        Placeholder::make('line_total')
                                            ->label('Total')
                                            ->content(function ($get) {
                                                $qty = floatval($get('quantity') ?? 0);
                                                $price = floatval($get('unit_price') ?? 0);
                                                $total = $qty * $price;
                                                return new HtmlString('<span class="text-lg font-bold">$' . number_format($total, 2) . '</span>');
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add Line Item')
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calculateTotals($state, $set, $get);
                            })
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Financial Summary')
                    ->description('Automatic calculations based on line items')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(3),
                            
                        TextInput::make('tax_rate')
                            ->label('Tax Rate (%)')
                            ->required()
                            ->numeric()
                            ->suffix('%')
                            ->default(8.25) // Texas state tax rate
                            ->reactive()
                            ->helperText('Texas state tax: 8.25%')
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calculateTotals($get('line_items'), $set, $get);
                            })
                            ->columnSpan(2),
                            
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(2),
                            
                        TextInput::make('discount_amount')
                            ->label('Discount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                self::calculateTotals($get('line_items'), $set, $get);
                            })
                            ->columnSpan(2),
                            
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->disabled()
                            ->dehydrated()
                            ->extraAttributes(['class' => 'text-xl font-bold'])
                            ->columnSpan(3),
                            
                        Grid::make(12)
                            ->schema([
                                TextInput::make('amount_paid')
                                    ->label('Amount Paid')
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $total = floatval($get('total_amount') ?? 0);
                                        $paid = floatval($state ?? 0);
                                        $set('balance_due', $total - $paid);
                                    })
                                    ->columnSpan(6),
                                    
                                TextInput::make('balance_due')
                                    ->label('Balance Due')
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->extraAttributes(['class' => 'text-lg font-semibold'])
                                    ->columnSpan(6),
                            ])
                            ->columnSpanFull(),
                    ]),
                    
                Section::make('Billing Information')
                    ->description('Customer billing address')
                    ->columnSpanFull()
                    ->columns(12)
                    ->collapsible()
                    ->components([
                        TextInput::make('billing_address')
                            ->label('Street Address')
                            ->columnSpan(6),
                            
                        TextInput::make('billing_city')
                            ->label('City')
                            ->columnSpan(2),
                            
                        TextInput::make('billing_parish')
                            ->label('State/Parish')
                            ->default('TX')
                            ->columnSpan(2),
                            
                        TextInput::make('billing_postal_code')
                            ->label('ZIP Code')
                            ->columnSpan(2),
                    ]),
                    
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        DatePicker::make('sent_date')
                            ->label('Date Sent')
                            ->columnSpan(3),
                            
                        DatePicker::make('paid_date')
                            ->label('Date Paid')
                            ->columnSpan(3),
                            
                        Toggle::make('is_recurring')
                            ->label('Recurring Invoice')
                            ->reactive()
                            ->columnSpan(3),
                            
                        Select::make('recurring_frequency')
                            ->label('Frequency')
                            ->options([
                                'weekly' => 'Weekly',
                                'biweekly' => 'Bi-weekly',
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'annually' => 'Annually',
                            ])
                            ->visible(fn ($get) => $get('is_recurring'))
                            ->columnSpan(3),
                            
                        Textarea::make('notes')
                            ->label('Internal Notes')
                            ->rows(3)
                            ->helperText('These notes are for internal use only')
                            ->columnSpan(6),
                            
                        Textarea::make('terms_conditions')
                            ->label('Terms & Conditions')
                            ->rows(3)
                            ->default('Payment is due within 30 days. A 1.5% monthly finance charge will be applied to overdue balances.')
                            ->columnSpan(6),
                    ]),
            ]);
    }
    
    protected static function calculateTotals($lineItems, $set, $get): void
    {
        $subtotal = 0;
        
        if (is_array($lineItems)) {
            foreach ($lineItems as $item) {
                $quantity = floatval($item['quantity'] ?? 0);
                $unitPrice = floatval($item['unit_price'] ?? 0);
                $subtotal += $quantity * $unitPrice;
            }
        }
        
        $taxRate = floatval($get('tax_rate') ?? 8.25);
        $taxAmount = $subtotal * ($taxRate / 100);
        $discount = floatval($get('discount_amount') ?? 0);
        $total = $subtotal + $taxAmount - $discount;
        
        $set('subtotal', round($subtotal, 2));
        $set('tax_amount', round($taxAmount, 2));
        $set('total_amount', round($total, 2));
        
        // Update balance due
        $amountPaid = floatval($get('amount_paid') ?? 0);
        $set('balance_due', round($total - $amountPaid, 2));
    }
}