<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->description('Basic information about the team member')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->maxLength(255)
                            ->rule(Password::default())
                            ->helperText(fn (string $operation): string => $operation === 'create'
                                    ? 'Leave blank to auto-generate a secure password and email it to the user'
                                    : 'Leave blank to keep current password')
                            ->placeholder(fn (string $operation): string => $operation === 'create'
                                    ? 'Auto-generate password'
                                    : ''),

                        Toggle::make('email_verified_at')
                            ->label('Email Verified')
                            ->helperText('Whether the user has verified their email address')
                            ->dehydrateStateUsing(fn ($state) => $state ? (is_string($state) ? $state : now()) : null)
                            ->afterStateHydrated(fn ($component, $state) => $component->state(filled($state)))
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Roles & Permissions')
                    ->description('Assign roles and permissions to control access')
                    ->schema([
                        Select::make('roles')
                            ->label('Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Roles define sets of permissions'),

                        Select::make('permissions')
                            ->label('Direct Permissions')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Additional permissions beyond those granted by roles'),
                    ])
                    ->columns(1),
            ]);
    }
}
