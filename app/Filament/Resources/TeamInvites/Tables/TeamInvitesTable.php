<?php

namespace App\Filament\Resources\TeamInvites\Tables;

use App\Mail\TeamInvitationMail;
use App\Models\TeamInvite;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;

class TeamInvitesTable
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
                    ->tooltip('Click to copy'),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not provided'),

                BadgeColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (TeamInvite $record): string => $record->getRoleDescription())
                    ->colors([
                        'danger' => 'super_admin',
                        'warning' => 'admin',
                        'primary' => 'manager',
                        'success' => ['dispatcher', 'driver'],
                        'info' => ['accountant', 'customer_service'],
                        'gray' => 'viewer',
                    ]),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->placeholder('All Companies'),

                IconColumn::make('status')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'expired' => 'heroicon-o-x-circle',
                        'accepted' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'expired' => 'danger',
                        'accepted' => 'success',
                        default => 'gray',
                    })
                    ->tooltip(fn (TeamInvite $record): string => match ($record->status) {
                        'pending' => 'Valid until '.$record->expires_at->format('M j, Y g:i A'),
                        'expired' => 'Expired on '.$record->expires_at->format('M j, Y g:i A'),
                        'accepted' => 'Accepted on '.$record->accepted_at->format('M j, Y g:i A'),
                        default => 'Unknown status',
                    }),

                TextColumn::make('invitedBy.name')
                    ->label('Invited By')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->color(fn (TeamInvite $record): string => $record->isExpired() ? 'danger' :
                        ($record->expires_at->isToday() ? 'warning' : 'gray')
                    ),

                TextColumn::make('accepted_at')
                    ->label('Accepted')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not accepted')
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'expired' => 'Expired',
                        'accepted' => 'Accepted',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! isset($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'pending' => $query->valid(),
                            'expired' => $query->expired(),
                            'accepted' => $query->accepted(),
                            default => $query,
                        };
                    }),

                SelectFilter::make('role')
                    ->label('Role')
                    ->options(TeamInvite::getAvailableRoles())
                    ->searchable(),

                Filter::make('expires_soon')
                    ->label('Expires Soon (24h)')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('expires_at', '<=', now()->addDay())
                        ->where('expires_at', '>', now())
                        ->whereNull('accepted_at')
                    ),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Resend Invitation')
                    ->modalDescription('This will regenerate the token and send another invitation email.')
                    ->action(function (TeamInvite $record) {
                        // Regenerate token and extend expiry
                        $record->regenerateToken();
                        $record->extendExpiration(7);

                        // Generate registration URL
                        $registrationUrl = route('team.register', ['token' => $record->token]);

                        try {
                            // Send the invitation email
                            Mail::to($record->email)->send(
                                new TeamInvitationMail($record, $registrationUrl)
                            );

                            Notification::make()
                                ->title('Invitation Resent')
                                ->body("Invitation has been resent to {$record->email}.")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to Resend')
                                ->body('There was an error resending the invitation email.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (TeamInvite $record) => ! $record->isAccepted()),

                EditAction::make(),

                DeleteAction::make()
                    ->label('Cancel')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Invitation')
                    ->modalDescription('This will permanently cancel this invitation.')
                    ->visible(fn (TeamInvite $record) => ! $record->isAccepted()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    Action::make('resend_bulk')
                        ->label('Resend Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Resend Invitations')
                        ->modalDescription('This will resend invitation emails to all selected recipients.')
                        ->action(function ($records) {
                            $sent = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                if (! $record->isAccepted()) {
                                    $record->regenerateToken();
                                    $record->extendExpiration(7);

                                    try {
                                        Mail::to($record->email)->send(
                                            new TeamInvitationMail($record, route('team.register', ['token' => $record->token]))
                                        );
                                        $sent++;
                                    } catch (\Exception $e) {
                                        $failed++;
                                    }
                                }
                            }

                            Notification::make()
                                ->title('Invitations Processed')
                                ->body("Sent: {$sent}, Failed: {$failed}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Cancel Selected')
                        ->modalHeading('Cancel Invitations')
                        ->modalDescription('This will permanently cancel the selected invitations.'),
                ]),
            ]);
    }
}
