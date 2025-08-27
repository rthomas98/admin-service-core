<?php

namespace App\Filament\Resources\MaintenanceLogs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Maintenance Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->columnSpan(3),
                        Select::make('equipment_id')
                            ->label('Equipment')
                            ->relationship('equipment', 'id')
                            ->required()
                            ->columnSpan(3),
                        Select::make('technician_id')
                            ->label('Technician')
                            ->relationship('technician', 'name')
                            ->columnSpan(3),
                        TextInput::make('service_type')
                            ->label('Service Type')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('service_date')
                            ->label('Service Date')
                            ->required()
                            ->columnSpan(4),
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->columnSpan(4),
                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->columnSpan(4),
                    ]),
                Section::make('Cost Breakdown')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('service_cost')
                            ->label('Service Cost')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('parts_cost')
                            ->label('Parts Cost')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('labor_cost')
                            ->label('Labor Cost')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                        TextInput::make('total_cost')
                            ->label('Total Cost')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->columnSpan(3),
                    ]),
                Section::make('Work Details')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('work_performed')
                            ->label('Work Performed')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        TextInput::make('parts_used')
                            ->label('Parts Used')
                            ->columnSpan(6),
                        TextInput::make('materials_used')
                            ->label('Materials Used')
                            ->columnSpan(6),
                        TextInput::make('condition_before')
                            ->label('Condition Before')
                            ->columnSpan(6),
                        TextInput::make('condition_after')
                            ->label('Condition After')
                            ->columnSpan(6),
                        TextInput::make('checklist_completed')
                            ->label('Checklist Completed')
                            ->columnSpan(6),
                        TextInput::make('photos')
                            ->label('Photos')
                            ->columnSpan(6),
                    ]),
                Section::make('Findings & Follow-up')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('issues_found')
                            ->label('Issues Found')
                            ->rows(3)
                            ->columnSpan(6),
                        Textarea::make('recommendations')
                            ->label('Recommendations')
                            ->rows(3)
                            ->columnSpan(6),
                        Toggle::make('requires_followup')
                            ->label('Requires Follow-up')
                            ->required()
                            ->columnSpan(4),
                        DatePicker::make('next_service_date')
                            ->label('Next Service Date')
                            ->columnSpan(4),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpan(4),
                    ]),
            ]);
    }
}
