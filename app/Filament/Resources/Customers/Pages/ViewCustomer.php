<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\Width;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Customer Details';

    public function getTitle(): string
    {
        return $this->record->organization ?: $this->record->name;
    }

    public function getSubheading(): ?string
    {
        return 'Customer #'.$this->record->customer_number.' • Member since '.$this->record->customer_since?->format('M d, Y');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_notification')
                ->label('Send Message')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->modalHeading('Send Customer Notification')
                ->form([
                    \Filament\Forms\Components\Select::make('type')
                        ->label('Message Type')
                        ->options([
                            'email' => 'Email',
                            'sms' => 'SMS',
                            'both' => 'Email & SMS',
                        ])
                        ->required()
                        ->default('email'),
                    \Filament\Forms\Components\TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->visible(fn ($get) => in_array($get('type'), ['email', 'both'])),
                    \Filament\Forms\Components\Textarea::make('message')
                        ->label('Message')
                        ->required()
                        ->rows(5)
                        ->maxLength(500),
                ])
                ->action(function (array $data) {
                    // Send notification logic here
                    $this->sendNotification($data);
                }),

            Action::make('generate_invoice')
                ->label('New Invoice')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.invoices.create', [
                    'tenant' => filament()->getTenant(),
                    'customer_id' => $this->record->id,
                ])),

            Action::make('create_quote')
                ->label('New Quote')
                ->icon('heroicon-o-calculator')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.quotes.create', [
                    'tenant' => filament()->getTenant(),
                    'customer_id' => $this->record->id,
                ])),

            EditAction::make()
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge),

            DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        // Left Column - Customer Details & Stats
                        Group::make([
                        // Customer Overview Section
                        Section::make('Customer Overview')
                            ->description('Basic information and contact details')
                            ->icon('heroicon-o-user')
                            ->collapsible()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('organization')
                                            ->label('Organization')
                                            ->weight(FontWeight::Bold)
                                            ->size('lg')
                                            ->default('N/A'),

                                        TextEntry::make('business_type')
                                            ->label('Customer Type')
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'Commercial' => 'success',
                                                'Residential' => 'info',
                                                'Government' => 'warning',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('name')
                                            ->label('Contact Name')
                                            ->icon('heroicon-m-user'),

                                        TextEntry::make('customer_number')
                                            ->label('Customer #')
                                            ->copyable()
                                            ->copyMessage('Customer number copied')
                                            ->copyMessageDuration(1500),

                                        TextEntry::make('phone')
                                            ->label('Primary Phone')
                                            ->icon('heroicon-m-phone')
                                            ->url(fn ($state) => $state ? "tel:{$state}" : null),

                                        TextEntry::make('secondary_phone')
                                            ->label('Secondary Phone')
                                            ->icon('heroicon-m-phone')
                                            ->url(fn ($state) => $state ? "tel:{$state}" : null)
                                            ->visible(fn ($record) => $record->secondary_phone),

                                        TextEntry::make('email')
                                            ->label('Email')
                                            ->icon('heroicon-m-envelope')
                                            ->url(fn ($state) => $state ? "mailto:{$state}" : null)
                                            ->copyable(),

                                        TextEntry::make('fax')
                                            ->label('Fax')
                                            ->icon('heroicon-m-printer')
                                            ->visible(fn ($record) => $record->fax),
                                    ]),
                            ]),

                        // Address Information
                        Section::make('Address Information')
                            ->description('Service and billing addresses')
                            ->icon('heroicon-o-map-pin')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('full_address')
                                            ->label('Primary Address')
                                            ->getStateUsing(fn ($record) => $record->address.', '.
                                                $record->city.', '.
                                                $record->state.' '.
                                                $record->zip
                                            )
                                            ->icon('heroicon-m-home')
                                            ->copyable(),

                                        TextEntry::make('county')
                                            ->label('County')
                                            ->visible(fn ($record) => $record->county),

                                        TextEntry::make('secondary_address')
                                            ->label('Secondary Address')
                                            ->icon('heroicon-m-building-office')
                                            ->visible(fn ($record) => $record->secondary_address),
                                    ]),
                            ]),

                        // Financial Summary
                        Section::make('Financial Summary')
                            ->description('Account balance and payment history')
                            ->icon('heroicon-o-currency-dollar')
                            ->collapsible()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('total_revenue')
                                            ->label('Total Revenue')
                                            ->getStateUsing(function ($record) {
                                                return '$'.number_format(
                                                    Invoice::where('customer_id', $record->id)
                                                        ->where('status', 'paid')
                                                        ->sum('total_amount'),
                                                    2
                                                );
                                            })
                                            ->weight(FontWeight::Bold)
                                            ->color('success')
                                            ->size('lg'),

                                        TextEntry::make('outstanding_balance')
                                            ->label('Outstanding Balance')
                                            ->getStateUsing(function ($record) {
                                                $balance = Invoice::where('customer_id', $record->id)
                                                    ->whereIn('status', ['pending', 'overdue'])
                                                    ->sum('total_amount');

                                                return '$'.number_format($balance, 2);
                                            })
                                            ->weight(FontWeight::Bold)
                                            ->color(fn ($state) => floatval(str_replace(['$', ','], '', $state)) > 0 ? 'danger' : 'gray'
                                            )
                                            ->size('lg'),

                                        TextEntry::make('average_invoice')
                                            ->label('Average Invoice')
                                            ->getStateUsing(function ($record) {
                                                $avg = Invoice::where('customer_id', $record->id)
                                                    ->avg('total_amount');

                                                return '$'.number_format($avg ?: 0, 2);
                                            })
                                            ->weight(FontWeight::Bold),

                                        TextEntry::make('total_invoices')
                                            ->label('Total Invoices')
                                            ->getStateUsing(fn ($record) => Invoice::where('customer_id', $record->id)->count()
                                            )
                                            ->badge()
                                            ->color('info'),

                                        TextEntry::make('paid_invoices')
                                            ->label('Paid Invoices')
                                            ->getStateUsing(fn ($record) => Invoice::where('customer_id', $record->id)
                                                ->where('status', 'paid')
                                                ->count()
                                            )
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('pending_invoices')
                                            ->label('Pending Invoices')
                                            ->getStateUsing(fn ($record) => Invoice::where('customer_id', $record->id)
                                                ->whereIn('status', ['pending', 'overdue'])
                                                ->count()
                                            )
                                            ->badge()
                                            ->color(fn ($state) => $state > 0 ? 'warning' : 'gray'),
                                    ]),
                            ]),

                        // Portal Access & Notifications
                        Section::make('Portal & Communication')
                            ->description('Customer portal access and notification preferences')
                            ->icon('heroicon-o-bell')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        IconEntry::make('portal_access')
                                            ->label('Portal Access')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-check-circle')
                                            ->falseIcon('heroicon-o-x-circle')
                                            ->trueColor('success')
                                            ->falseColor('danger'),

                                        IconEntry::make('notifications_enabled')
                                            ->label('Notifications Enabled')
                                            ->boolean()
                                            ->trueIcon('heroicon-o-bell')
                                            ->falseIcon('heroicon-o-bell-slash')
                                            ->trueColor('success')
                                            ->falseColor('gray'),

                                        TextEntry::make('preferred_notification_method')
                                            ->label('Preferred Contact')
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'email' => 'info',
                                                'sms' => 'success',
                                                'phone' => 'warning',
                                                default => 'gray',
                                            }),

                                        IconEntry::make('sms_verified')
                                            ->label('SMS Verified')
                                            ->boolean()
                                            ->visible(fn ($record) => $record->sms_number),
                                    ]),
                            ]),
                    ])->columnSpan(1),

                    // Right Column - Activity & Related Records
                    Group::make([
                        // Quick Actions
                        Section::make('Quick Actions')
                            ->description('Common customer actions')
                            ->collapsible()
                            ->schema([
                                ViewEntry::make('quick_actions')
                                    ->view('filament.resources.customers.quick-actions'),
                            ]),

                        // Recent Activity Timeline
                        Section::make('Recent Activity')
                            ->description('Customer interactions and transactions')
                            ->icon('heroicon-o-clock')
                            ->collapsible()
                            ->schema([
                                ViewEntry::make('activity_timeline')
                                    ->view('filament.resources.customers.activity-timeline'),
                            ]),

                        // Service Orders
                        Section::make('Recent Service Orders')
                            ->description('Latest service requests and orders')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                RepeatableEntry::make('serviceOrders')
                                    ->label('')
                                    ->getStateUsing(fn ($record) => $record->serviceOrders()->limit(5)->get())
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('order_number')
                                                    ->label('Order #')
                                                    ->weight(FontWeight::Bold),
                                                TextEntry::make('delivery_date')
                                                    ->label('Delivery Date')
                                                    ->date(),
                                                TextEntry::make('status')
                                                    ->badge()
                                                    ->color(fn ($state) => match ($state) {
                                                        'completed' => 'success',
                                                        'in_progress' => 'warning',
                                                        'scheduled' => 'info',
                                                        'cancelled' => 'danger',
                                                        default => 'gray',
                                                    }),
                                            ]),
                                    ]),
                            ]),

                        // Notes & Internal Memos
                        Section::make('Notes & Memos')
                            ->description('Internal notes and customer messages')
                            ->icon('heroicon-o-document-text')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                TextEntry::make('internal_memo')
                                    ->label('Internal Memo')
                                    ->html()
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => $record->internal_memo),

                                TextEntry::make('external_message')
                                    ->label('Customer Message')
                                    ->html()
                                    ->columnSpanFull()
                                    ->visible(fn ($record) => $record->external_message),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    protected function sendNotification(array $data): void
    {
        // Implement notification sending logic
        // This would integrate with your notification service

        \Filament\Notifications\Notification::make()
            ->title('Notification sent')
            ->body("Message sent to customer via {$data['type']}")
            ->success()
            ->send();
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Add customer-specific widgets here if needed
        ];
    }
}
