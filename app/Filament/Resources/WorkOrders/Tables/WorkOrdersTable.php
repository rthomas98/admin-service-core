<?php

namespace App\Filament\Resources\WorkOrders\Tables;

use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderAction;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('service_date', 'desc')
            ->columns([
                TextColumn::make('ticket_number')
                    ->label('Ticket #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('service_date')
                    ->label('Service Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')->badge()
                    ->label('Status')
                    ->color(fn ($state): string => $state?->color() ?? 'gray'),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('customer_name')
                    ->label('Customer (Manual)')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('driver.full_name')
                    ->label('Driver')
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('driver', function ($q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy(
                            \App\Models\Driver::select(\Illuminate\Support\Facades\DB::raw("CONCAT(first_name, ' ', last_name)"))
                                ->whereColumn('drivers.id', 'work_orders.driver_id'),
                            $direction
                        );
                    })
                    ->toggleable(),

                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('container_size')
                    ->label('Container')
                    ->toggleable(),

                TextColumn::make('waste_type')
                    ->label('Waste Type')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('truck_number')
                    ->label('Truck #')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('dispatch_number')
                    ->label('Dispatch #')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('po_number')
                    ->label('P.O. #')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('time_on_site')
                    ->label('Time On')
                    ->time()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('time_off_site')
                    ->label('Time Off')
                    ->time()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cod_amount')
                    ->label('COD')
                    ->money('usd')
                    ->sortable()
                    ->toggleable()
                    ->state(fn ($record) => $record->cod_amount ?: null),

                TextColumn::make('full_address')
                    ->label('Service Address')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%")
                            ->orWhere('state', 'like', "%{$search}%")
                            ->orWhere('zip', 'like', "%{$search}%");
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('container_delivered')
                    ->label('Delivered')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('container_picked_up')
                    ->label('Picked Up')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('disposal_ticket')
                    ->label('Disposal Ticket')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('signatures_status')->badge()
                    ->label('Signatures')
                    ->getStateUsing(function ($record) {
                        if ($record->isFullySigned()) {
                            return 'Both Signed';
                        } elseif ($record->hasCustomerSignature()) {
                            return 'Customer Only';
                        } elseif ($record->hasDriverSignature()) {
                            return 'Driver Only';
                        }
                        return 'None';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Both Signed' => 'success',
                        'Customer Only', 'Driver Only' => 'warning',
                        'None' => 'gray',
                        default => 'gray'
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('completed_at')
                    ->label('Completed')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(WorkOrderStatus::class),

                SelectFilter::make('action')
                    ->options(WorkOrderAction::class),

                SelectFilter::make('driver_id')
                    ->label('Driver')
                    ->options(function () {
                        return \App\Models\Driver::query()
                            ->where('company_id', \Filament\Facades\Filament::getTenant()->id)
                            ->orderBy('first_name')
                            ->orderBy('last_name')
                            ->get()
                            ->pluck('full_name', 'id');
                    })
                    ->searchable(),

                SelectFilter::make('customer_id')
                    ->relationship('customer', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name)
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn ($record) => WorkOrderResource::getUrl('view', ['record' => $record])
            );
    }
}