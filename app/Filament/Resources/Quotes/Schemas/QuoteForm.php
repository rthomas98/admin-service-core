<?php

namespace App\Filament\Resources\Quotes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Quote Management')
                    ->tabs([
                        Tabs\Tab::make('Customer Information')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Contact Details')
                                    ->description('Information from the quote request')
                                    ->columns(12)
                                    ->components([
                                        TextInput::make('quote_number')
                                            ->label('Quote Number')
                                            ->disabled()
                                            ->columnSpan(3),
                                            
                                        Select::make('status')
                                            ->label('Status')
                                            ->options([
                                                'new' => 'New Request',
                                                'pending' => 'Pending Review',
                                                'sent' => 'Sent to Customer',
                                                'accepted' => 'Accepted',
                                                'rejected' => 'Rejected',
                                                'expired' => 'Expired',
                                            ])
                                            ->required()
                                            ->columnSpan(3),
                                            
                                        DatePicker::make('quote_date')
                                            ->label('Quote Date')
                                            ->disabled()
                                            ->columnSpan(3),
                                            
                                        DatePicker::make('valid_until')
                                            ->label('Valid Until')
                                            ->minDate(now())
                                            ->default(now()->addDays(30))
                                            ->columnSpan(3),
                                            
                                        TextInput::make('name')
                                            ->label('Customer Name')
                                            ->required()
                                            ->columnSpan(4),
                                            
                                        TextInput::make('company')
                                            ->label('Company')
                                            ->columnSpan(4),
                                            
                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required()
                                            ->columnSpan(4),
                                            
                                        TextInput::make('phone')
                                            ->label('Phone')
                                            ->tel()
                                            ->required()
                                            ->columnSpan(3),
                                            
                                        TextInput::make('location')
                                            ->label('Location')
                                            ->required()
                                            ->columnSpan(3),
                                            
                                        Select::make('project_type')
                                            ->label('Project Type')
                                            ->options([
                                                'Construction' => 'Construction',
                                                'Event' => 'Event',
                                                'Commercial' => 'Commercial',
                                                'Residential' => 'Residential',
                                                'Industrial' => 'Industrial',
                                                'Government/Municipal' => 'Government/Municipal',
                                                'Emergency Response' => 'Emergency Response',
                                            ])
                                            ->required()
                                            ->columnSpan(3),
                                            
                                        DatePicker::make('start_date')
                                            ->label('Project Start Date')
                                            ->required()
                                            ->columnSpan(3),
                                            
                                        TextInput::make('duration')
                                            ->label('Duration')
                                            ->columnSpan(6),
                                            
                                        TagsInput::make('services')
                                            ->label('Services Requested')
                                            ->suggestions([
                                                'Roll-Off Dumpsters',
                                                'Portable Toilets',
                                                'Handwash Stations',
                                                'Holding Tanks',
                                                'Water Tanks',
                                            ])
                                            ->columnSpan(6),
                                            
                                        Textarea::make('message')
                                            ->label('Customer Message')
                                            ->rows(3)
                                            ->disabled()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Quote Details')
                            ->icon('heroicon-o-currency-dollar')
                            ->schema([
                                Section::make('Service Items & Pricing')
                                    ->description('Add service items with pricing details')
                                    ->components([
                                        Repeater::make('items')
                                            ->label('Quote Items')
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
                                                                'pickup' => 'Pickup',
                                                                'other' => 'Other',
                                                            ])
                                                            ->required()
                                                            ->columnSpan(3),
                                                            
                                                        TextInput::make('description')
                                                            ->label('Description')
                                                            ->required()
                                                            ->placeholder('e.g., 20-yard roll-off dumpster')
                                                            ->columnSpan(5),
                                                            
                                                        TextInput::make('quantity')
                                                            ->label('Qty')
                                                            ->numeric()
                                                            ->required()
                                                            ->default(1)
                                                            ->minValue(1)
                                                            ->reactive()
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
                                            ->defaultItems(0)
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
                                    
                                Section::make('Totals & Financial Details')
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
                                            ->default(8.25)
                                            ->reactive()
                                            ->helperText('Louisiana tax rate')
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                self::calculateTotals($get('items'), $set, $get);
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
                                                self::calculateTotals($get('items'), $set, $get);
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
                                    ]),
                            ]),
                            
                        Tabs\Tab::make('Delivery & Terms')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Section::make('Delivery Information')
                                    ->columns(12)
                                    ->components([
                                        TextInput::make('delivery_address')
                                            ->label('Delivery Address')
                                            ->columnSpan(6),
                                            
                                        TextInput::make('delivery_city')
                                            ->label('City')
                                            ->columnSpan(2),
                                            
                                        TextInput::make('delivery_parish')
                                            ->label('State/Parish')
                                            ->default('LA')
                                            ->columnSpan(2),
                                            
                                        TextInput::make('delivery_postal_code')
                                            ->label('ZIP Code')
                                            ->columnSpan(2),
                                            
                                        DatePicker::make('requested_delivery_date')
                                            ->label('Delivery Date')
                                            ->columnSpan(3),
                                            
                                        DatePicker::make('requested_pickup_date')
                                            ->label('Pickup Date')
                                            ->columnSpan(3),
                                    ]),
                                    
                                Section::make('Terms & Conditions')
                                    ->components([
                                        Textarea::make('terms_conditions')
                                            ->label('Terms & Conditions')
                                            ->rows(4)
                                            ->default('1. Payment is due within 30 days of invoice date.
2. A 1.5% monthly finance charge will be applied to overdue balances.
3. Customer is responsible for any damage to equipment beyond normal wear and tear.
4. Prices are subject to change based on disposal fees and fuel surcharges.
5. Equipment must be accessible for delivery and pickup.'),
                                            
                                        Textarea::make('notes')
                                            ->label('Internal Notes')
                                            ->rows(3)
                                            ->helperText('These notes are for internal use only and will not be sent to the customer'),
                                            
                                        Textarea::make('description')
                                            ->label('Quote Description')
                                            ->rows(3)
                                            ->placeholder('Add a description of the services and scope of work')
                                            ->helperText('This will be included in the quote sent to the customer'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
    
    protected static function calculateTotals($items, $set, $get): void
    {
        $subtotal = 0;
        
        if (is_array($items)) {
            foreach ($items as $item) {
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
    }
}