<?php

namespace App\Filament\Resources\TeamInvites\Schemas;

use App\Models\TeamInvite;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamInviteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invitation Details')
                    ->description('Send an invitation to a new team member')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Email address of the team member to invite'),

                        TextInput::make('name')
                            ->label('Full Name')
                            ->maxLength(255)
                            ->helperText('Optional: Include their name for a personal touch'),

                        Select::make('role')
                            ->label('Role')
                            ->options(TeamInvite::getAvailableRoles())
                            ->required()
                            ->searchable()
                            ->helperText('Select the role this team member will have')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => $set('role_description', TeamInvite::getAvailableRoles()[$state] ?? '')),

                        Placeholder::make('role_description')
                            ->label('Role Description')
                            ->content(fn ($get) => TeamInvite::getAvailableRoles()[$get('role')] ?? 'Select a role to see its description')
                            ->visible(fn ($get) => filled($get('role'))),

                        Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Optional: Assign to a specific company')
                            ->visible(fn () => auth()->user()->hasRole('super_admin')),

                        DateTimePicker::make('expires_at')
                            ->label('Invitation Expires')
                            ->required()
                            ->default(now()->addDays(7))
                            ->minDate(now()->addHour())
                            ->maxDate(now()->addDays(30))
                            ->helperText('How long the invitation link will be valid'),

                        Textarea::make('message')
                            ->label('Personal Message')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Optional: Add a personal welcome message to the invitation email'),
                    ])
                    ->columns(2),

                Section::make('Additional Permissions')
                    ->description('Optionally grant specific permissions beyond the role')
                    ->schema([
                        Select::make('permissions')
                            ->label('Extra Permissions')
                            ->options([
                                'manage_users' => 'Manage Users',
                                'manage_vehicles' => 'Manage Vehicles',
                                'manage_drivers' => 'Manage Drivers',
                                'manage_customers' => 'Manage Customers',
                                'manage_invoices' => 'Manage Invoices',
                                'manage_quotes' => 'Manage Quotes',
                                'manage_routes' => 'Manage Routes',
                                'view_reports' => 'View Reports',
                                'export_data' => 'Export Data',
                            ])
                            ->multiple()
                            ->searchable()
                            ->helperText('Select any additional permissions this user should have'),
                    ])
                    ->collapsed()
                    ->columns(1),

                Section::make('Invitation Status')
                    ->description('Current status of this invitation')
                    ->schema([
                        Placeholder::make('status')
                            ->label('Status')
                            ->content(fn (?TeamInvite $record): string => $record ? ucfirst($record->status) : 'New Invitation'),

                        Placeholder::make('invited_by')
                            ->label('Invited By')
                            ->content(fn (?TeamInvite $record): string => $record?->invitedBy?->name ?? auth()->user()->name),

                        Placeholder::make('created_at')
                            ->label('Sent On')
                            ->content(fn (?TeamInvite $record): string => $record?->created_at?->format('M j, Y g:i A') ?? '-'),

                        Placeholder::make('accepted_at')
                            ->label('Accepted On')
                            ->content(fn (?TeamInvite $record): string => $record?->accepted_at?->format('M j, Y g:i A') ?? 'Not yet accepted'),

                        Placeholder::make('token')
                            ->label('Invitation Token')
                            ->content(fn (?TeamInvite $record): string => $record?->token ?? 'Will be generated on save')
                            ->visible(fn () => auth()->user()->hasRole(['super_admin', 'admin'])),
                    ])
                    ->columns(2)
                    ->visible(fn ($operation) => $operation === 'edit'),
            ]);
    }
}
