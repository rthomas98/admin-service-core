<?php

namespace App\Filament\Resources\CompanyUsers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('company_id')
                                    ->relationship('company', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('password')
                                    ->password()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->maxLength(255)
                                    ->dehydrated(fn (?string $state): bool => filled($state)),
                                Select::make('role')
                                    ->options([
                                        'admin' => 'Admin',
                                        'manager' => 'Manager',
                                        'staff' => 'Staff',
                                        'viewer' => 'Viewer',
                                    ])
                                    ->required(),
                            ]),
                    ]),
                Section::make('Access Control')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true),
                                Toggle::make('portal_access')
                                    ->label('Portal Access')
                                    ->default(true),
                            ]),
                    ]),
                Section::make('Permissions')
                    ->schema([
                        Select::make('permissions')
                            ->multiple()
                            ->options([
                                'view_dashboard' => 'View Dashboard',
                                'manage_users' => 'Manage Users',
                                'manage_vehicles' => 'Manage Vehicles',
                                'manage_drivers' => 'Manage Drivers',
                                'manage_customers' => 'Manage Customers',
                                'manage_invoices' => 'Manage Invoices',
                                'manage_quotes' => 'Manage Quotes',
                                'manage_waste' => 'Manage Waste Operations',
                                'view_reports' => 'View Reports',
                                'manage_settings' => 'Manage Settings',
                            ])
                            ->helperText('Select specific permissions for this user'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
