<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerInviteResource\Pages;
use App\Mail\CustomerInvitationMail;
use App\Models\CustomerInvite;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use UnitEnum;

class CustomerInviteResource extends Resource
{
    protected static ?string $model = CustomerInvite::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Customer Invitations';

    protected static string|UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Invitation Details')
                    ->description('Send an invitation to a customer to access their portal. They will receive the "customer" role and can view invoices, submit service requests, and manage their account.')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->helperText('Email address of the customer'),

                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Optional: Link to existing customer record'),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->required()
                            ->default(now()->addDays(7))
                            ->minDate(now())
                            ->helperText('When this invitation will expire'),
                    ])
                    ->columns(2),

                Section::make('Status Information')
                    ->schema([
                        Forms\Components\Placeholder::make('token')
                            ->content(fn (?CustomerInvite $record): string => $record?->token ?? 'Generated on save'),

                        Forms\Components\Placeholder::make('invited_by')
                            ->content(fn (?CustomerInvite $record): string => $record?->invitedBy?->name ?? auth()->user()->name),

                        Forms\Components\Placeholder::make('created_at')
                            ->content(fn (?CustomerInvite $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                        Forms\Components\Placeholder::make('accepted_at')
                            ->content(fn (?CustomerInvite $record): string => $record?->accepted_at?->diffForHumans() ?? 'Not accepted'),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.full_name')
                    ->label('Customer')
                    ->searchable(['customers.name', 'customers.organization', 'customers.first_name', 'customers.last_name'])
                    ->sortable()
                    ->placeholder('No customer linked'),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->tooltip('Click to copy'),

                Tables\Columns\IconColumn::make('expiration_status')
                    ->label('Status')
                    ->icon(fn (string $state): string => match ($state) {
                        'valid' => 'heroicon-o-clock',
                        'expired' => 'heroicon-o-x-circle',
                        'accepted' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'valid' => 'warning',
                        'expired' => 'danger',
                        'accepted' => 'success',
                        default => 'gray',
                    })
                    ->tooltip(fn (CustomerInvite $record): string => match ($record->expiration_status) {
                        'valid' => 'Valid until '.$record->expires_at->format('M j, Y g:i A'),
                        'expired' => 'Expired on '.$record->expires_at->format('M j, Y g:i A'),
                        'accepted' => 'Accepted on '.$record->accepted_at->format('M j, Y g:i A'),
                        default => 'Unknown status',
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable()
                    ->color(fn (CustomerInvite $record): string => $record->isExpired() ? 'danger' : ($record->expires_at->isToday() ? 'warning' : 'gray')
                    ),

                Tables\Columns\TextColumn::make('invitedBy.name')
                    ->label('Invited By')
                    ->sortable(),

                Tables\Columns\TextColumn::make('accepted_at')
                    ->label('Accepted')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not accepted')
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('expiration_status')
                    ->label('Status')
                    ->options([
                        'valid' => 'Valid',
                        'expired' => 'Expired',
                        'accepted' => 'Accepted',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! isset($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'valid' => $query->valid(),
                            'expired' => $query->expired(),
                            'accepted' => $query->accepted(),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('linked_customer')
                    ->label('Has Linked Customer')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('customer_id')),

                Tables\Filters\Filter::make('expires_soon')
                    ->label('Expires Soon (24h)')
                    ->query(fn (Builder $query): Builder => $query->where('expires_at', '<=', now()->addDay())
                        ->where('expires_at', '>', now())
                        ->whereNull('accepted_at')
                    ),
            ])
            ->actions([
                Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Resend Invitation')
                    ->modalDescription('This will regenerate the token and send another invitation email.')
                    ->action(function (CustomerInvite $record) {
                        // Regenerate token and extend expiry
                        $record->regenerateToken();
                        $record->extendExpiration(7);

                        // Generate registration URL
                        $registrationUrl = route('customer.register.form', [
                            'token' => $record->token,
                        ]);

                        try {
                            // Resend the invitation email
                            Mail::to($record->email)->send(
                                new CustomerInvitationMail($record, $registrationUrl)
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
                    ->visible(fn (CustomerInvite $record) => ! $record->isAccepted()),

                ViewAction::make(),

                Action::make('delete')
                    ->label('Cancel')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Invitation')
                    ->modalDescription('This will permanently cancel this invitation. The recipient will no longer be able to use this link to register.')
                    ->action(fn (CustomerInvite $record) => $record->delete())
                    ->visible(fn (CustomerInvite $record) => ! $record->isAccepted()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Action::make('resend_bulk')
                        ->label('Resend Selected')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Resend Invitations')
                        ->modalDescription('This will resend invitation emails to all selected recipients.')
                        ->action(function ($records) {
                            $result = CustomerInvite::resendBulk($records->pluck('id')->toArray());

                            Notification::make()
                                ->title('Invitations Processed')
                                ->body("Sent: {$result['sent']->count()}, Failed: {$result['failed']->count()}")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Action::make('extend_expiry')
                        ->label('Extend Expiry')
                        ->icon('heroicon-o-clock')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('days')
                                ->label('Days to Extend')
                                ->numeric()
                                ->default(7)
                                ->minValue(1)
                                ->maxValue(30)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (! $record->isAccepted()) {
                                    $record->extendExpiration($data['days']);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title('Expiry Extended')
                                ->body("Extended {$count} invitations by {$data['days']} days.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Action::make('deactivate_bulk')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (! $record->isAccepted()) {
                                    $record->deactivate();
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title('Invitations Deactivated')
                                ->body("Deactivated {$count} invitations.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make()
                        ->label('Delete Selected')
                        ->modalHeading('Delete Invitations')
                        ->modalDescription('This will permanently delete the selected invitations.'),
                ]),
            ])
            ->recordUrl(
                fn ($record) => static::getUrl('view', ['record' => $record])
            );
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomerInvites::route('/'),
            'create' => Pages\CreateCustomerInvite::route('/create'),
            'view' => Pages\ViewCustomerInvite::route('/{record}'),
            'edit' => Pages\EditCustomerInvite::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $tenant = Filament::getTenant();

        if ($tenant) {
            $query->where('company_id', $tenant->id);
        }

        return $query;
    }

    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();

        // Only show for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }

    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();

        // Only allow creation for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }

    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();

        // Only allow editing for RAW Disposal company and if not accepted
        return $tenant && $tenant->isRawDisposal() && ! $record->isAccepted();
    }

    public static function canDelete(Model $record): bool
    {
        $tenant = Filament::getTenant();

        // Only allow deletion for RAW Disposal company and if not accepted
        return $tenant && $tenant->isRawDisposal() && ! $record->isAccepted();
    }
}
