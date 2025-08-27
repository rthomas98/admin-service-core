<?php

namespace App\Filament\Resources\Equipment\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EquipmentForm
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
                        TextInput::make('type')
                            ->label('Equipment Type')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('size')
                            ->label('Size')
                            ->columnSpan(3),
                        TextInput::make('unit_number')
                            ->label('Unit Number')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('available')
                            ->columnSpan(4),
                        TextInput::make('condition')
                            ->label('Condition')
                            ->required()
                            ->default('good')
                            ->columnSpan(4),
                        TextInput::make('color')
                            ->label('Color')
                            ->columnSpan(4),
                        TextInput::make('current_location')
                            ->label('Current Location')
                            ->columnSpan(4),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->columnSpan(4),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->columnSpan(4),
                    ]),
                Section::make('Service & Financial Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        DatePicker::make('purchase_date')
                            ->label('Purchase Date')
                            ->columnSpan(3),
                        DatePicker::make('last_service_date')
                            ->label('Last Service Date')
                            ->columnSpan(3),
                        DatePicker::make('next_service_due')
                            ->label('Next Service Due')
                            ->columnSpan(3),
                        TextInput::make('purchase_price')
                            ->label('Purchase Price')
                            ->numeric()
                            ->prefix('$')
                            ->columnSpan(3),
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
