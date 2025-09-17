<?php

namespace App\Filament\Resources\CompanyUserInvites\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CompanyUserInviteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Hidden::make('company_id')
                    ->default(fn () => Filament::getTenant()?->id)
                    ->required(),

                Hidden::make('invited_by')
                    ->default(fn () => auth()->id())
                    ->required(),

                Section::make('Invitation Details')
                    ->description('Invite internal users or company owners to manage the system')
                    ->columns(2)
                    ->components([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique('company_user_invites', 'email', ignoreRecord: true)
                            ->placeholder('user@example.com')
                            ->columnSpan(1),

                        TextInput::make('name')
                            ->label('Full Name')
                            ->placeholder('John Doe')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Select::make('role')
                            ->label('User Role')
                            ->options([
                                'admin' => 'Administrator (Full system access)',
                                'company' => 'Company Owner (Customer who owns their business)',
                                'manager' => 'Manager (Can manage operations)',
                                'staff' => 'Staff (Limited access)',
                                'viewer' => 'Viewer (Read-only access)',
                            ])
                            ->default('staff')
                            ->required()
                            ->helperText('Company Owner role is for customers who will manage their own company profile.')
                            ->columnSpan(1),

                        DateTimePicker::make('expires_at')
                            ->label('Invitation Expires')
                            ->default(now()->addDays(7))
                            ->minDate(now())
                            ->required()
                            ->helperText('The invitation link will expire after this date')
                            ->columnSpan(1),
                    ]),

                Section::make('Permissions')
                    ->description('Optional: Set specific permissions for this user')
                    ->collapsed()
                    ->components([
                        TagsInput::make('permissions')
                            ->label('Custom Permissions')
                            ->placeholder('Type permission and press Enter')
                            ->suggestions([
                                'view_invoices',
                                'edit_invoices',
                                'delete_invoices',
                                'view_customers',
                                'edit_customers',
                                'delete_customers',
                                'view_service_requests',
                                'edit_service_requests',
                                'delete_service_requests',
                                'view_reports',
                                'export_data',
                                'manage_users',
                            ])
                            ->helperText('Add specific permissions for this user. Admin role has all permissions by default.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
