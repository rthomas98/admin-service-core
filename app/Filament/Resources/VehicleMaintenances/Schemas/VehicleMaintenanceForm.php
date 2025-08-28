<?php

namespace App\Filament\Resources\VehicleMaintenances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleMaintenanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Maintenance Details')
                ->description('Basic information about the maintenance record')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('maintenance_number')
                            ->label('Maintenance #')
                            ->default(fn() => \App\Models\VehicleMaintenance::generateMaintenanceNumber())
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        
                        Select::make('vehicle_id')
                            ->label('Vehicle')
                            ->relationship('vehicle', 'unit_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('driver_id')
                            ->label('Driver/Technician')
                            ->relationship('driver', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        
                        Select::make('maintenance_type')
                            ->label('Maintenance Type')
                            ->options([
                                'preventive' => 'Preventive',
                                'corrective' => 'Corrective',
                                'emergency' => 'Emergency',
                                'scheduled' => 'Scheduled',
                                'oil_change' => 'Oil Change',
                                'tire_rotation' => 'Tire Rotation',
                                'brake_service' => 'Brake Service',
                                'inspection' => 'Inspection',
                                'other' => 'Other'
                            ])
                            ->required()
                            ->native(false),
                        
                        Select::make('priority')
                            ->label('Priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent'
                            ])
                            ->default('medium')
                            ->required()
                            ->native(false),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'on_hold' => 'On Hold'
                            ])
                            ->default('scheduled')
                            ->required()
                            ->native(false),
                    ]),
                ])->columns(1),

            Section::make('Schedule & Completion')
                ->description('Scheduling and completion details')
                ->schema([
                    Grid::make(2)->schema([
                        DatePicker::make('scheduled_date')
                            ->label('Scheduled Date')
                            ->required()
                            ->native(false),
                        
                        TimePicker::make('scheduled_time')
                            ->label('Scheduled Time')
                            ->native(false),
                        
                        DatePicker::make('completed_date')
                            ->label('Completed Date')
                            ->nullable()
                            ->native(false),
                        
                        TimePicker::make('completed_time')
                            ->label('Completed Time')
                            ->nullable()
                            ->native(false),
                        
                        TextInput::make('odometer_at_service')
                            ->label('Odometer at Service')
                            ->numeric()
                            ->suffix('miles')
                            ->nullable(),
                        
                        TextInput::make('next_service_miles')
                            ->label('Next Service Miles')
                            ->numeric()
                            ->suffix('miles')
                            ->nullable(),
                        
                        DatePicker::make('next_service_date')
                            ->label('Next Service Date')
                            ->nullable()
                            ->native(false),
                    ]),
                ])->columns(1),

            Section::make('Work Details')
                ->description('Details of the maintenance work performed')
                ->schema([
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(3)
                        ->required(),
                    
                    Repeater::make('work_performed')
                        ->label('Work Performed')
                        ->schema([
                            TextInput::make('task')
                                ->label('Task')
                                ->required(),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'completed' => 'Completed',
                                    'partial' => 'Partial',
                                    'pending' => 'Pending'
                                ])
                                ->default('completed')
                                ->required(),
                            TextInput::make('notes')
                                ->label('Notes'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->collapsible(),
                    
                    Repeater::make('parts_replaced')
                        ->label('Parts Replaced')
                        ->schema([
                            TextInput::make('part_name')
                                ->label('Part Name')
                                ->required(),
                            TextInput::make('part_number')
                                ->label('Part Number'),
                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->default(1)
                                ->required(),
                            TextInput::make('cost')
                                ->label('Cost')
                                ->numeric()
                                ->prefix('$'),
                        ])
                        ->columns(4)
                        ->defaultItems(0)
                        ->collapsible(),
                    
                    Repeater::make('fluids_added')
                        ->label('Fluids Added')
                        ->schema([
                            TextInput::make('fluid_type')
                                ->label('Fluid Type')
                                ->required(),
                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->suffix('quarts')
                                ->required(),
                            TextInput::make('cost')
                                ->label('Cost')
                                ->numeric()
                                ->prefix('$'),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->collapsible(),
                ])->columns(1),

            Section::make('Service Provider')
                ->description('Information about the service provider')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('service_provider')
                            ->label('Service Provider')
                            ->nullable(),
                        
                        TextInput::make('technician_name')
                            ->label('Technician Name')
                            ->nullable(),
                        
                        TextInput::make('work_order_number')
                            ->label('Work Order Number')
                            ->nullable(),
                        
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->nullable(),
                    ]),
                ])->columns(1),

            Section::make('Costs & Warranty')
                ->description('Cost breakdown and warranty information')
                ->schema([
                    Grid::make(3)->schema([
                        TextInput::make('labor_cost')
                            ->label('Labor Cost')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                $set('total_cost', $get('labor_cost') + $get('parts_cost') + $get('other_cost'))),
                        
                        TextInput::make('parts_cost')
                            ->label('Parts Cost')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                $set('total_cost', $get('labor_cost') + $get('parts_cost') + $get('other_cost'))),
                        
                        TextInput::make('other_cost')
                            ->label('Other Cost')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                $set('total_cost', $get('labor_cost') + $get('parts_cost') + $get('other_cost'))),
                        
                        TextInput::make('total_cost')
                            ->label('Total Cost')
                            ->numeric()
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(),
                        
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'partial' => 'Partial',
                                'warranty' => 'Under Warranty'
                            ])
                            ->default('pending')
                            ->native(false),
                        
                        Toggle::make('under_warranty')
                            ->label('Under Warranty')
                            ->reactive(),
                        
                        TextInput::make('warranty_claim_number')
                            ->label('Warranty Claim #')
                            ->visible(fn (callable $get) => $get('under_warranty'))
                            ->nullable(),
                        
                        TextInput::make('warranty_covered_amount')
                            ->label('Warranty Covered Amount')
                            ->numeric()
                            ->prefix('$')
                            ->visible(fn (callable $get) => $get('under_warranty'))
                            ->nullable(),
                    ]),
                ])->columns(1),

            Section::make('Vehicle Downtime')
                ->description('Track vehicle downtime during maintenance')
                ->schema([
                    Grid::make(3)->schema([
                        DateTimePicker::make('vehicle_down_from')
                            ->label('Vehicle Down From')
                            ->nullable()
                            ->native(false),
                        
                        DateTimePicker::make('vehicle_down_to')
                            ->label('Vehicle Down To')
                            ->nullable()
                            ->native(false),
                        
                        TextInput::make('total_downtime_hours')
                            ->label('Total Downtime (Hours)')
                            ->numeric()
                            ->disabled()
                            ->suffix('hours'),
                    ]),
                ])->columns(1),

            Section::make('Additional Information')
                ->description('Notes and recommendations')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notes')
                        ->rows(3)
                        ->nullable(),
                    
                    Textarea::make('recommendations')
                        ->label('Recommendations')
                        ->rows(3)
                        ->nullable(),
                ])->columns(1),
        ]);
    }
}