<?php

namespace App\Filament\Resources\VehicleFinances\Schemas;

use App\Enums\FinanceType;
use App\Models\FinanceCompany;
use App\Models\Vehicle;
use App\Models\Trailer;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VehicleFinanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Finance Details')
                    ->components([
                        MorphToSelect::make('financeable')
                            ->label('Asset')
                            ->types([
                                MorphToSelect\Type::make(Vehicle::class)
                                    ->titleAttribute('unit_number')
                                    ->label('Vehicle'),
                                MorphToSelect\Type::make(Trailer::class)
                                    ->titleAttribute('unit_number')
                                    ->label('Trailer'),
                            ])
                            ->required()
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        
                        Grid::make(2)
                            ->components([
                                Select::make('finance_company_id')
                                    ->label('Finance Company')
                                    ->relationship('financeCompany', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required(),
                                        TextInput::make('phone'),
                                        TextInput::make('email')
                                            ->email(),
                                    ]),
                                Select::make('finance_type')
                                    ->label('Finance Type')
                                    ->options(FinanceType::class)
                                    ->default(FinanceType::Loan)
                                    ->required()
                                    ->searchable(),
                            ]),
                        
                        Grid::make(2)
                            ->components([
                                TextInput::make('account_number')
                                    ->label('Account Number')
                                    ->placeholder('e.g., ACC-123456'),
                                TextInput::make('reference_number')
                                    ->label('Reference Number')
                                    ->placeholder('e.g., REF-789012'),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Terms & Schedule')
                    ->components([
                        Grid::make(2)
                            ->components([
                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->required()
                                    ->default(now()),
                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->after('start_date'),
                            ]),
                        
                        Grid::make(3)
                            ->components([
                                TextInput::make('monthly_payment')
                                    ->label('Monthly Payment')
                                    ->numeric()
                                    ->prefix('$')
                                    ->required()
                                    ->placeholder('e.g., 1500.00'),
                                TextInput::make('total_amount')
                                    ->label('Total Amount')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('e.g., 75000.00'),
                                TextInput::make('down_payment')
                                    ->label('Down Payment')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('e.g., 15000.00'),
                            ]),
                        
                        Grid::make(3)
                            ->components([
                                TextInput::make('interest_rate')
                                    ->label('Interest Rate')
                                    ->numeric()
                                    ->suffix('%')
                                    ->placeholder('e.g., 5.25'),
                                TextInput::make('term_months')
                                    ->label('Term (Months)')
                                    ->numeric()
                                    ->placeholder('e.g., 60'),
                                TextInput::make('residual_value')
                                    ->label('Residual Value')
                                    ->numeric()
                                    ->prefix('$')
                                    ->placeholder('e.g., 10000.00'),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Additional Information')
                    ->components([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Any additional information about this financing...')
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Mark as inactive when financing is paid off or terminated')
                            ->default(true)
                            ->required(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}