<?php

namespace App\Filament\Resources\ServiceSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ServiceScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Service Information')
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
                        TextInput::make('service_type')
                            ->label('Service Type')
                            ->required()
                            ->columnSpan(3),
                        TextInput::make('status')
                            ->label('Status')
                            ->required()
                            ->default('scheduled')
                            ->columnSpan(3),
                        Select::make('technician_id')
                            ->label('Technician')
                            ->relationship('technician', 'name')
                            ->columnSpan(3),
                        TextInput::make('priority')
                            ->label('Priority')
                            ->required()
                            ->default('normal')
                            ->columnSpan(3),
                        DateTimePicker::make('scheduled_datetime')
                            ->label('Scheduled Date/Time')
                            ->required()
                            ->columnSpan(3),
                        DateTimePicker::make('completed_datetime')
                            ->label('Completed Date/Time')
                            ->columnSpan(3),
                        TextInput::make('estimated_duration_minutes')
                            ->label('Estimated Duration (minutes)')
                            ->numeric()
                            ->columnSpan(3),
                        TextInput::make('actual_duration_minutes')
                            ->label('Actual Duration (minutes)')
                            ->numeric()
                            ->columnSpan(3),
                    ]),
                Section::make('Service Details')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('service_description')
                            ->label('Service Description')
                            ->columnSpan(6)
                            ->rows(3),
                        Textarea::make('completion_notes')
                            ->label('Completion Notes')
                            ->columnSpan(6)
                            ->rows(3),
                        TextInput::make('checklist_items')
                            ->label('Checklist Items')
                            ->columnSpan(4),
                        TextInput::make('materials_used')
                            ->label('Materials Used')
                            ->columnSpan(4),
                        TextInput::make('photos')
                            ->label('Photos')
                            ->columnSpan(4),
                    ]),
                Section::make('Cost & Follow-up')
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
                        TextInput::make('materials_cost')
                            ->label('Materials Cost')
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
                        Toggle::make('requires_followup')
                            ->label('Requires Follow-up')
                            ->required()
                            ->columnSpan(3),
                        DatePicker::make('followup_date')
                            ->label('Follow-up Date')
                            ->columnSpan(6),
                    ]),
            ]);
    }
}
