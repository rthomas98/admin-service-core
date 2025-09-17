<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Actions\SendPortalInviteAction;
use App\Filament\Resources\CustomerResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_number')
                    ->label('Customer #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Customer number copied')
                    ->weight('bold'),

                TextColumn::make('organization')
                    ->label('Organization')
                    ->searchable(['organization', 'name', 'first_name', 'last_name'])
                    ->sortable()
                    ->wrap()
                    ->description(fn ($record) => $record->full_name ?: null)
                    ->limit(30),

                TextColumn::make('business_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Commercial' => 'success',
                        'Residential' => 'info',
                        'Government' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Contact')
                    ->searchable()
                    ->description(fn ($record) => $record->emails ? (is_array($record->emails) ? $record->emails[0] ?? null : null) : null)
                    ->copyable()
                    ->formatStateUsing(fn ($state, $record) => $record->display_phone)
                    ->icon('heroicon-m-phone'),

                TextColumn::make('location')
                    ->label('Location')
                    ->getStateUsing(fn ($record) => "{$record->city}, {$record->state}")
                    ->searchable(query: function ($query, string $search): void {
                        $query->where('city', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%");
                    })
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderBy('state', $direction)
                            ->orderBy('city', $direction);
                    }),

                TextColumn::make('serviceOrders_count')
                    ->label('Orders')
                    ->counts('serviceOrders')
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('outstanding_balance')
                    ->label('Balance')
                    ->getStateUsing(function ($record) {
                        $balance = $record->invoices()
                            ->whereIn('status', ['pending', 'overdue'])
                            ->sum('total_amount');

                        return $balance > 0 ? '$'.number_format($balance, 2) : '—';
                    })
                    ->color(fn ($state) => $state !== '—' ? 'danger' : 'gray')
                    ->weight(fn ($state) => $state !== '—' ? 'bold' : null)
                    ->alignEnd(),

                IconColumn::make('portal_access')
                    ->label('Portal')
                    ->boolean()
                    ->sortable()
                    ->tooltip(fn ($record) => $record->portal_access ? 'Has portal access' : 'No portal access'),

                TextColumn::make('customer_since')
                    ->label('Since')
                    ->date('M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('last_order_date')
                    ->label('Last Order')
                    ->getStateUsing(function ($record) {
                        $lastOrder = $record->serviceOrders()
                            ->latest('created_at')
                            ->first();

                        return $lastOrder?->created_at?->diffForHumans() ?? 'Never';
                    })
                    ->color(fn ($state) => $state === 'Never' ? 'gray' : null)
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderBy(
                            \DB::raw('(SELECT MAX(created_at) FROM service_orders WHERE customer_id = customers.id)'),
                            $direction
                        );
                    }),
            ])
            ->defaultSort('customer_since', 'desc')
            ->filters([
                SelectFilter::make('business_type')
                    ->label('Customer Type')
                    ->options([
                        'Commercial' => 'Commercial',
                        'Residential' => 'Residential',
                        'Government' => 'Government',
                    ])
                    ->multiple(),

                SelectFilter::make('state')
                    ->label('State')
                    ->options(fn () => \App\Models\Customer::distinct()
                        ->pluck('state', 'state')
                        ->filter()
                        ->sort()
                        ->toArray())
                    ->searchable(),

                Filter::make('has_balance')
                    ->label('Has Outstanding Balance')
                    ->query(fn ($query) => $query->whereHas('invoices', function ($q) {
                        $q->whereIn('status', ['pending', 'overdue']);
                    })),

                Filter::make('active_customers')
                    ->label('Active (Last 3 Months)')
                    ->query(fn ($query) => $query->whereHas('serviceOrders', function ($q) {
                        $q->where('service_date', '>=', now()->subMonths(3));
                    })),

                Filter::make('inactive_customers')
                    ->label('Inactive (6+ Months)')
                    ->query(fn ($query) => $query->whereDoesntHave('serviceOrders', function ($q) {
                        $q->where('service_date', '>=', now()->subMonths(6));
                    })),

                SelectFilter::make('portal_access')
                    ->label('Portal Access')
                    ->options([
                        '1' => 'Has Access',
                        '0' => 'No Access',
                    ])
                    ->query(function ($query, array $data) {
                        if (isset($data['value'])) {
                            return $query->where('portal_access', (bool) $data['value']);
                        }

                        return $query;
                    }),

                Filter::make('customer_since')
                    ->form([
                        DatePicker::make('from')
                            ->label('From'),
                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function ($query, array $data): void {
                        $query
                            ->when(
                                $data['from'],
                                fn ($query, $date) => $query->whereDate('customer_since', '>=', $date)
                            )
                            ->when(
                                $data['until'],
                                fn ($query, $date) => $query->whereDate('customer_since', '<=', $date)
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Customer since '.\Carbon\Carbon::parse($data['from'])->toFormattedDateString();
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Customer until '.\Carbon\Carbon::parse($data['until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    SendPortalInviteAction::make(),
                    Action::make('send_message')
                        ->label('Send Message')
                        ->icon('heroicon-o-envelope')
                        ->color('gray')
                        ->modalHeading(fn ($record) => 'Send Message to '.($record->organization ?: $record->full_name))
                        ->form([
                            \Filament\Forms\Components\Select::make('type')
                                ->label('Message Type')
                                ->options([
                                    'email' => 'Email',
                                    'sms' => 'SMS',
                                ])
                                ->required()
                                ->default('email'),
                            \Filament\Forms\Components\TextInput::make('subject')
                                ->label('Subject')
                                ->required()
                                ->visible(fn ($get) => $get('type') === 'email'),
                            \Filament\Forms\Components\Textarea::make('message')
                                ->label('Message')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(function ($record, array $data) {
                            // Send message logic here
                            \Filament\Notifications\Notification::make()
                                ->title('Message sent')
                                ->body('Message sent to '.($record->organization ?: $record->full_name))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->recordUrl(
                fn ($record) => CustomerResource::getUrl('view', ['record' => $record])
            )
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('send_bulk_message')
                        ->label('Send Message')
                        ->icon('heroicon-o-envelope')
                        ->modalHeading('Send Bulk Message')
                        ->form([
                            \Filament\Forms\Components\Select::make('type')
                                ->label('Message Type')
                                ->options([
                                    'email' => 'Email',
                                    'sms' => 'SMS',
                                ])
                                ->required()
                                ->default('email'),
                            \Filament\Forms\Components\TextInput::make('subject')
                                ->label('Subject')
                                ->required()
                                ->visible(fn ($get) => $get('type') === 'email'),
                            \Filament\Forms\Components\Textarea::make('message')
                                ->label('Message')
                                ->required()
                                ->rows(4)
                                ->helperText('This message will be sent to all selected customers.'),
                        ])
                        ->action(function ($records, array $data) {
                            $count = $records->count();
                            // Bulk message logic here
                            \Filament\Notifications\Notification::make()
                                ->title('Messages queued')
                                ->body("Sending messages to {$count} customers")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('export')
                        ->label('Export Selected')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function ($records) {
                            // Export logic here
                            \Filament\Notifications\Notification::make()
                                ->title('Export started')
                                ->body("Exporting {$records->count()} customers")
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->searchPlaceholder('Search by name, organization, phone, email, or customer #')
            ->striped()
            ->poll('60s');
    }
}
