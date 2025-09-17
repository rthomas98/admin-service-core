<?php

namespace App\Filament\Resources\WasteRoutes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WasteRouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Route Information')
                    ->description('Basic information about the waste collection route')
                    ->schema([
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required(),
                        TextInput::make('name')
                            ->required()
                            ->placeholder('Route Name (e.g., Downtown Monday Route)'),
                        TextInput::make('zone')
                            ->placeholder('Service Zone'),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'maintenance' => 'Under Maintenance',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),

                Section::make('Schedule')
                    ->description('Route scheduling and timing')
                    ->schema([
                        Select::make('frequency')
                            ->options([
                                'daily' => 'Daily',
                                'weekly' => 'Weekly',
                                'biweekly' => 'Bi-Weekly',
                                'monthly' => 'Monthly',
                                'on_demand' => 'On Demand',
                            ])
                            ->required()
                            ->default('weekly'),
                        TextInput::make('estimated_duration_hours')
                            ->numeric()
                            ->suffix('hours')
                            ->minValue(0)
                            ->step(0.5),
                        TextInput::make('total_distance_km')
                            ->numeric()
                            ->suffix('km')
                            ->minValue(0),
                    ])
                    ->columns(3),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3)
                            ->placeholder('Route description, special instructions, or notes'),
                    ]),
            ]);
    }
}
