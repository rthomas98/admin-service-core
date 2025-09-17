<?php

namespace App\Filament\Resources\DisposalSites\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DisposalSiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site Information')
                    ->description('Basic information about the disposal site')
                    ->schema([
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required(),
                        TextInput::make('name')
                            ->required(),
                        Select::make('site_type')
                            ->options([
                                'landfill' => 'Landfill',
                                'transfer_station' => 'Transfer Station',
                                'recycling_center' => 'Recycling Center',
                                'composting' => 'Composting Facility',
                            ])
                            ->required()
                            ->default('landfill'),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'maintenance' => 'Under Maintenance',
                                'closed' => 'Closed',
                            ])
                            ->required()
                            ->default('active'),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->description('Site location details')
                    ->schema([
                        TextInput::make('location')
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('parish')
                            ->placeholder('Parish/County'),
                    ])
                    ->columns(2),

                Section::make('Capacity Management')
                    ->description('Site capacity and intake information')
                    ->schema([
                        TextInput::make('total_capacity')
                            ->required()
                            ->numeric()
                            ->suffix('tons'),
                        TextInput::make('current_capacity')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->suffix('tons'),
                        TextInput::make('daily_intake_average')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->suffix('tons/day'),
                    ])
                    ->columns(3),

                Section::make('Site Management')
                    ->description('Contact and operational information')
                    ->schema([
                        TextInput::make('manager_name'),
                        TextInput::make('contact_phone')
                            ->tel(),
                        TextInput::make('operating_hours')
                            ->placeholder('Mon-Fri 7am-5pm'),
                    ])
                    ->columns(3),

                Section::make('Compliance')
                    ->description('Permits and inspection information')
                    ->schema([
                        TextInput::make('environmental_permit'),
                        DatePicker::make('last_inspection_date'),
                    ])
                    ->columns(2),
            ]);
    }
}
