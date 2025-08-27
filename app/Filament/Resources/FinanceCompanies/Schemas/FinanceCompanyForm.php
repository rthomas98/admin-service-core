<?php

namespace App\Filament\Resources\FinanceCompanies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FinanceCompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Company Information')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Company Name')
                                    ->required()
                                    ->placeholder('e.g., Wells Fargo Equipment Finance'),
                                TextInput::make('account_number')
                                    ->label('Account Number')
                                    ->placeholder('e.g., 123456789'),
                            ]),
                        Grid::make(2)
                            ->components([
                                TextInput::make('contact_name')
                                    ->label('Primary Contact')
                                    ->placeholder('e.g., John Smith'),
                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->placeholder('e.g., (555) 123-4567'),
                            ]),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->placeholder('e.g., contact@financecompany.com')
                            ->columnSpanFull(),
                        TextInput::make('website')
                            ->label('Website')
                            ->url()
                            ->placeholder('e.g., https://www.financecompany.com')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Address')
                    ->components([
                        TextInput::make('address')
                            ->label('Street Address')
                            ->placeholder('e.g., 123 Main Street')
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->components([
                                TextInput::make('city')
                                    ->label('City')
                                    ->placeholder('e.g., New Orleans'),
                                TextInput::make('state')
                                    ->label('State')
                                    ->maxLength(2)
                                    ->placeholder('e.g., LA'),
                                TextInput::make('zip')
                                    ->label('ZIP Code')
                                    ->placeholder('e.g., 70112'),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Additional Information')
                    ->components([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Any additional information about this finance company...')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive companies will not be available for new financing')
                            ->default(true)
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}