<?php

namespace App\Filament\Resources\VehicleInspections\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleInspectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Inspection Details')
                ->description('Basic information about the vehicle inspection')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('inspection_number')
                            ->label('Inspection Number')
                            ->default(fn() => \App\Models\VehicleInspection::generateInspectionNumber())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        
                        Select::make('vehicle_id')
                            ->label('Vehicle')
                            ->relationship('vehicle', 'unit_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select vehicle'),
                        
                        Select::make('driver_id')
                            ->label('Driver/Inspector')
                            ->relationship('driver', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Select driver/inspector'),
                        
                        Select::make('inspection_type')
                            ->label('Inspection Type')
                            ->options([
                                'pre_trip' => 'Pre-Trip',
                                'post_trip' => 'Post-Trip',
                                'annual' => 'Annual',
                                'dot' => 'DOT',
                                'safety' => 'Safety',
                                'maintenance' => 'Maintenance'
                            ])
                            ->required()
                            ->native(false),
                        
                        DatePicker::make('inspection_date')
                            ->label('Inspection Date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        
                        TimePicker::make('inspection_time')
                            ->label('Inspection Time')
                            ->default(now()->format('H:i'))
                            ->native(false),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'needs_repair' => 'Needs Repair'
                            ])
                            ->default('scheduled')
                            ->required()
                            ->native(false),
                        
                        TextInput::make('odometer_reading')
                            ->label('Odometer Reading')
                            ->numeric()
                            ->suffix('miles')
                            ->nullable(),
                        
                        // Placeholder for grid balance
                        \Filament\Forms\Components\Placeholder::make('details_spacer')->label(''),
                    ]),
                ])->columns(1),

            Section::make('Inspection Checklist')
                ->description('Items to inspect during the vehicle check')
                ->schema([
                    Repeater::make('exterior_items')
                            ->label('Exterior Items')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Item')
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pass' => 'Pass',
                                        'fail' => 'Fail',
                                        'needs_attention' => 'Needs Attention'
                                    ])
                                    ->required(),
                                TextInput::make('notes')
                                    ->label('Notes')
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible(),
                        
                        Repeater::make('interior_items')
                            ->label('Interior Items')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Item')
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pass' => 'Pass',
                                        'fail' => 'Fail',
                                        'needs_attention' => 'Needs Attention'
                                    ])
                                    ->required(),
                                TextInput::make('notes')
                                    ->label('Notes')
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible(),
                        
                        Repeater::make('engine_items')
                            ->label('Engine Items')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Item')
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pass' => 'Pass',
                                        'fail' => 'Fail',
                                        'needs_attention' => 'Needs Attention'
                                    ])
                                    ->required(),
                                TextInput::make('notes')
                                    ->label('Notes')
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible(),
                        
                        Repeater::make('safety_items')
                            ->label('Safety Items')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Item')
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pass' => 'Pass',
                                        'fail' => 'Fail',
                                        'needs_attention' => 'Needs Attention'
                                    ])
                                    ->required(),
                                TextInput::make('notes')
                                    ->label('Notes')
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible(),
                    
                    Repeater::make('documentation_items')
                            ->label('Documentation Items')
                            ->schema([
                                TextInput::make('item')
                                    ->label('Item')
                                    ->required(),
                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pass' => 'Pass',
                                        'fail' => 'Fail',
                                        'needs_attention' => 'Needs Attention'
                                    ])
                                    ->required(),
                                TextInput::make('notes')
                                    ->label('Notes')
                                    ->nullable(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible(),
                ])->columns(1),

            Section::make('Issues & Actions')
                ->description('Document any issues found and corrective actions taken')
                ->schema([
                    Repeater::make('issues_found')
                        ->label('Issues Found')
                        ->schema([
                            TextInput::make('issue')
                                ->label('Issue Description')
                                ->required(),
                            Select::make('severity')
                                ->label('Severity')
                                ->options([
                                    'minor' => 'Minor',
                                    'moderate' => 'Moderate',
                                    'major' => 'Major',
                                    'critical' => 'Critical'
                                ])
                                ->required(),
                            Textarea::make('action_required')
                                ->label('Action Required')
                                ->rows(2)
                                ->nullable(),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible(),
                    
                    Textarea::make('corrective_actions')
                        ->label('Corrective Actions Taken')
                        ->rows(3)
                        ->nullable(),
                    
                    Textarea::make('notes')
                        ->label('Additional Notes')
                        ->rows(3)
                        ->nullable(),
                ])->columns(1),

            Section::make('Inspector Information')
                ->description('Details about the inspector and certification')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('inspector_name')
                            ->label('Inspector Name')
                            ->required(),
                        
                        TextInput::make('inspector_certification_number')
                            ->label('Certification Number')
                            ->nullable(),
                        
                        DateTimePicker::make('certified_at')
                            ->label('Certified At')
                            ->nullable()
                            ->native(false),
                        
                        TextInput::make('inspector_signature')
                            ->label('Inspector Signature')
                            ->nullable()
                            ->placeholder('Digital signature or initials'),
                        
                        // Placeholders for grid balance
                        \Filament\Forms\Components\Placeholder::make('inspector_spacer_1')->label(''),
                        \Filament\Forms\Components\Placeholder::make('inspector_spacer_2')->label(''),
                    ]),
                ])->columns(1),

            Section::make('Next Inspection')
                ->description('Schedule the next inspection')
                ->schema([
                    Grid::make(3)->schema([
                        DatePicker::make('next_inspection_date')
                            ->label('Next Inspection Date')
                            ->nullable()
                            ->native(false),
                        
                        TextInput::make('next_inspection_miles')
                            ->label('Next Inspection Miles')
                            ->numeric()
                            ->suffix('miles')
                            ->nullable(),
                        
                        // Placeholder for grid balance
                        \Filament\Forms\Components\Placeholder::make('next_inspection_spacer')->label(''),
                    ]),
                ])->columns(1),
        ]);
    }
}