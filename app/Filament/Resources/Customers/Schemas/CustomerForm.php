<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Hidden::make('company_id')
                    ->default(fn () => Filament::getTenant()->id),

                Section::make('Customer Information')
                    ->description('Basic customer details and contact information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('customer_number')
                            ->label('Customer Number')
                            ->columnSpan(3),

                        DatePicker::make('customer_since')
                            ->label('Customer Since')
                            ->columnSpan(3),

                        TextInput::make('organization')
                            ->label('Organization/Company')
                            ->columnSpan(6),

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->columnSpan(4),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->columnSpan(4),

                        TextInput::make('name')
                            ->label('Display Name')
                            ->columnSpan(4),

                        TextInput::make('emails')
                            ->label('Email')
                            ->email()
                            ->columnSpan(4),

                        TextInput::make('phone')
                            ->label('Primary Phone')
                            ->tel()
                            ->columnSpan(4),

                        TextInput::make('phone_ext')
                            ->label('Primary Phone Ext')
                            ->placeholder('Ext')
                            ->columnSpan(2),

                        TextInput::make('secondary_phone')
                            ->label('Secondary Phone')
                            ->tel()
                            ->columnSpan(4),

                        TextInput::make('secondary_phone_ext')
                            ->label('Secondary Phone Ext')
                            ->placeholder('Ext')
                            ->columnSpan(2),

                        TextInput::make('fax')
                            ->label('Fax Number')
                            ->columnSpan(4),

                        TextInput::make('fax_ext')
                            ->label('Fax Ext')
                            ->placeholder('Ext')
                            ->columnSpan(2),
                    ]),

                Section::make('Primary Address')
                    ->description('Primary service and billing address')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('address')
                            ->label('Street Address')
                            ->rows(2)
                            ->columnSpan(12),

                        TextInput::make('city')
                            ->label('City')
                            ->columnSpan(4),

                        TextInput::make('state')
                            ->label('State')
                            ->maxLength(2)
                            ->columnSpan(2),

                        TextInput::make('zip')
                            ->label('ZIP Code')
                            ->columnSpan(3),

                        TextInput::make('county')
                            ->label('County')
                            ->columnSpan(3),
                    ]),

                Section::make('Secondary Address')
                    ->description('Secondary or alternate address')
                    ->columnSpanFull()
                    ->columns(12)
                    ->collapsible()
                    ->collapsed()
                    ->components([
                        Textarea::make('secondary_address')
                            ->label('Secondary Address')
                            ->rows(2)
                            ->columnSpan(12),
                    ]),

                Section::make('Business Information')
                    ->description('Additional business details')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('delivery_method')
                            ->label('Delivery Method')
                            ->columnSpan(4),

                        TextInput::make('referral')
                            ->label('Referral Source')
                            ->columnSpan(4),

                        TextInput::make('external_id')
                            ->label('External ID')
                            ->columnSpan(4),

                        TextInput::make('business_type')
                            ->label('Business Type')
                            ->columnSpan(6),

                        TextInput::make('divisions')
                            ->label('Divisions')
                            ->columnSpan(6),

                        TextInput::make('tax_code_name')
                            ->label('Tax Code')
                            ->columnSpan(4),

                        Textarea::make('tax_exemption_details')
                            ->label('Tax Exemption Details')
                            ->rows(2)
                            ->columnSpan(4),

                        Textarea::make('tax_exempt_reason')
                            ->label('Tax Exempt Reason')
                            ->rows(2)
                            ->columnSpan(4),
                    ]),

                Section::make('Notes & Communication')
                    ->description('Internal and external messages')
                    ->columnSpanFull()
                    ->columns(12)
                    ->collapsible()
                    ->components([
                        Textarea::make('external_message')
                            ->label('External Message')
                            ->rows(3)
                            ->helperText('This message will be visible to the customer')
                            ->columnSpan(6),

                        Textarea::make('internal_memo')
                            ->label('Internal Memo')
                            ->rows(3)
                            ->helperText('For internal use only - not visible to customer')
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
