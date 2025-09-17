<?php

namespace App\Filament\Resources\CompanyUserInvites\Tables;

use App\Mail\CompanyUserInviteEmail;
use App\Models\CompanyUserInvite;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CompanyUserInvitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied'),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->default('Not provided'),

                BadgeColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'company' => 'Company Owner',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'company' => 'primary',
                        'manager' => 'warning',
                        'staff' => 'success',
                        'viewer' => 'gray',
                        default => 'secondary',
                    }),

                TextColumn::make('invitedBy.name')
                    ->label('Invited By')
                    ->sortable(),

                IconColumn::make('accepted_at')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(fn (CompanyUserInvite $record): string => $record->accepted_at
                            ? 'Accepted on '.$record->accepted_at->format('M j, Y')
                            : 'Pending acceptance'
                    ),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->color(fn (CompanyUserInvite $record): string => $record->isExpired() ? 'danger' : 'secondary'
                    ),

                TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'company' => 'Company Owner',
                        'manager' => 'Manager',
                        'staff' => 'Staff',
                        'viewer' => 'Viewer',
                    ]),

                TernaryFilter::make('accepted')
                    ->label('Status')
                    ->placeholder('All invitations')
                    ->trueLabel('Accepted')
                    ->falseLabel('Pending')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('accepted_at'),
                        false: fn ($query) => $query->whereNull('accepted_at'),
                    ),

                TernaryFilter::make('expired')
                    ->label('Expiration')
                    ->placeholder('All')
                    ->trueLabel('Expired')
                    ->falseLabel('Active')
                    ->queries(
                        true: fn ($query) => $query->where('expires_at', '<', now()),
                        false: fn ($query) => $query->where('expires_at', '>=', now()),
                    ),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->visible(fn (CompanyUserInvite $record): bool => ! $record->isAccepted() && ! $record->isExpired()
                    )
                    ->requiresConfirmation()
                    ->action(function (CompanyUserInvite $record): void {
                        // Generate new token and extend expiration
                        $record->update([
                            'token' => Str::random(32),
                            'expires_at' => now()->addDays(7),
                        ]);

                        // Send the email
                        Mail::to($record->email)->send(new CompanyUserInviteEmail($record));

                        Notification::make()
                            ->title('Invitation Resent')
                            ->body("Invitation has been resent to {$record->email}")
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->visible(fn (CompanyUserInvite $record): bool => ! $record->isAccepted()),

                DeleteAction::make()
                    ->visible(fn (CompanyUserInvite $record): bool => ! $record->isAccepted()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()->can('delete_company_user_invites')),
                ]),
            ]);
    }
}
