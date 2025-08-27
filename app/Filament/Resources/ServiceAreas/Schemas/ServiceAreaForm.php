<?php

namespace App\Filament\Resources\ServiceAreas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Service Area Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('name')
                            ->label('Service Area Name')
                            ->required()
                            ->columnSpan(3),
                        Toggle::make('is_active')
                            ->label('Is Active')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('priority')
                            ->label('Priority')
                            ->required()
                            ->numeric()
                            ->default(100)
                            ->columnSpan(3),
                        TextInput::make('zip_codes')
                            ->label('ZIP Codes')
                            ->columnSpan(4),
                        TextInput::make('parishes')
                            ->label('Parishes')
                            ->columnSpan(4),
                        TextInput::make('boundaries')
                            ->label('Boundaries')
                            ->columnSpan(4),
                    ]),
                Section::make('Pricing & Delivery')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('delivery_surcharge')
                            ->label('Delivery Surcharge')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('pickup_surcharge')
                            ->label('Pickup Surcharge')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('emergency_surcharge')
                            ->label('Emergency Surcharge')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                        TextInput::make('standard_delivery_days')
                            ->label('Standard Delivery (Days)')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->columnSpan(4),
                        TextInput::make('rush_delivery_hours')
                            ->label('Rush Delivery (Hours)')
                            ->numeric()
                            ->columnSpan(4),
                        TextInput::make('rush_delivery_surcharge')
                            ->label('Rush Delivery Surcharge')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(4),
                    ]),
                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpan(6),
                        Textarea::make('service_notes')
                            ->label('Service Notes')
                            ->rows(4)
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
