<?php

namespace App\Filament\Resources\WorkOrders\Schemas;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderAction;
use App\Enums\TimePeriod;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\ServiceOrder;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(\App\Models\WorkOrder::class)
            ->columns(12)
            ->components([
                Hidden::make('company_id')
                    ->default(fn () => Filament::getTenant()->id),

                Section::make('Work Order Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('ticket_number')
                            ->label('Ticket #')
                            ->placeholder('Auto-generated if blank')
                            ->columnSpan(3)
                            ->maxLength(255),

                        TextInput::make('po_number')
                            ->label('P.O. Number')
                            ->columnSpan(3)
                            ->maxLength(255),

                        DatePicker::make('service_date')
                            ->label('Service Date')
                            ->required()
                            ->default(now())
                            ->columnSpan(3),

                        Select::make('status')
                            ->label('Status')
                            ->options(WorkOrderStatus::class)
                            ->default(WorkOrderStatus::DRAFT)
                            ->required()
                            ->columnSpan(3),

                        TimePicker::make('time_on_site')
                            ->label('Time On Site')
                            ->seconds(false)
                            ->columnSpan(3),

                        Select::make('time_on_site_period')
                            ->label('Period')
                            ->options(TimePeriod::class)
                            ->columnSpan(2),

                        TimePicker::make('time_off_site')
                            ->label('Time Off Site')
                            ->seconds(false)
                            ->columnSpan(3),

                        Select::make('time_off_site_period')
                            ->label('Period')
                            ->options(TimePeriod::class)
                            ->columnSpan(4),
                    ]),

                Section::make('Assignment Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('truck_number')
                            ->label('Truck Number')
                            ->columnSpan(4)
                            ->maxLength(255),

                        TextInput::make('dispatch_number')
                            ->label('Dispatch Number')
                            ->columnSpan(4)
                            ->maxLength(255),

                        Select::make('driver_id')
                            ->label('Driver')
                            ->options(function () {
                                return \App\Models\Driver::query()
                                    ->where('company_id', Filament::getTenant()->id)
                                    ->orderBy('first_name')
                                    ->orderBy('last_name')
                                    ->get()
                                    ->pluck('full_name', 'id');
                            })
                            ->searchable()
                            ->columnSpan(4),
                    ]),

                Section::make('Customer Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(function () {
                                return Customer::where('company_id', Filament::getTenant()->id)
                                    ->get()
                                    ->mapWithKeys(function ($customer) {
                                        $displayName = $customer->organization 
                                            ?? $customer->name 
                                            ?? trim("{$customer->first_name} {$customer->last_name}")
                                            ?? "Customer #{$customer->id}";
                                        return [$customer->id => $displayName];
                                    });
                            })
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $customer = Customer::find($state);
                                    if ($customer) {
                                        $set('customer_name', $customer->name);
                                        $set('address', $customer->service_address);
                                        $set('city', $customer->service_city);
                                        $set('state', $customer->service_state);
                                        $set('zip', $customer->service_zip);
                                    }
                                }
                            })
                            ->columnSpan(6),

                        TextInput::make('customer_name')
                            ->label('Customer Name (Manual Entry)')
                            ->columnSpan(6)
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label('Service Address')
                            ->columnSpan(12)
                            ->maxLength(255),

                        TextInput::make('city')
                            ->label('City')
                            ->columnSpan(6)
                            ->maxLength(255),

                        TextInput::make('state')
                            ->label('State')
                            ->maxLength(2)
                            ->columnSpan(3),

                        TextInput::make('zip')
                            ->label('ZIP Code')
                            ->maxLength(10)
                            ->columnSpan(3),
                    ]),

                Section::make('Service Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Select::make('action')
                            ->label('Action')
                            ->options(WorkOrderAction::class)
                            ->default(WorkOrderAction::SERVICE)
                            ->required()
                            ->columnSpan(4),

                        TextInput::make('container_size')
                            ->label('Container Size')
                            ->placeholder('e.g., 10 yard, 20 yard, 30 yard')
                            ->columnSpan(4)
                            ->maxLength(255),

                        TextInput::make('waste_type')
                            ->label('Waste Type')
                            ->placeholder('e.g., Construction Debris, Household')
                            ->columnSpan(4)
                            ->maxLength(255),

                        Textarea::make('service_description')
                            ->label('Service Description')
                            ->rows(3)
                            ->columnSpan(12),

                        TextInput::make('container_delivered')
                            ->label('Container # Delivered')
                            ->columnSpan(6)
                            ->maxLength(255),

                        TextInput::make('container_picked_up')
                            ->label('Container # Picked Up')
                            ->columnSpan(6)
                            ->maxLength(255),

                        TextInput::make('disposal_id')
                            ->label('Disposal ID')
                            ->columnSpan(6)
                            ->maxLength(255),

                        TextInput::make('disposal_ticket')
                            ->label('Disposal Ticket')
                            ->columnSpan(6)
                            ->maxLength(255),
                    ]),

                Section::make('Payment & Documentation')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        TextInput::make('cod_amount')
                            ->label('COD Amount')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->columnSpan(6),

                        TextInput::make('cod_signature')
                            ->label('COD Signature Reference')
                            ->columnSpan(6)
                            ->maxLength(255)
                            ->helperText('Signature capture will be available in mobile app'),

                        TextInput::make('customer_signature')
                            ->label('Customer Signature Reference')
                            ->columnSpan(6)
                            ->maxLength(255)
                            ->disabled()
                            ->helperText('Captured via mobile app'),

                        TextInput::make('driver_signature')
                            ->label('Driver Signature Reference')
                            ->columnSpan(6)
                            ->maxLength(255)
                            ->disabled()
                            ->helperText('Captured via mobile app'),
                    ]),

                Section::make('Additional Information')
                    ->columnSpanFull()
                    ->columns(12)
                    ->components([
                        Textarea::make('comments')
                            ->label('Comments')
                            ->rows(4)
                            ->columnSpanFull(),

                        Select::make('service_order_id')
                            ->label('Related Service Order')
                            ->options(function () {
                                return ServiceOrder::where('company_id', Filament::getTenant()->id)
                                    ->get()
                                    ->mapWithKeys(function ($order) {
                                        $label = $order->order_number ?? "Service Order #{$order->id}";
                                        return [$order->id => $label];
                                    });
                            })
                            ->searchable()
                            ->columnSpanFull()
                            ->helperText('Optional: Link this work order to a service order'),
                    ]),
            ]);
    }
}