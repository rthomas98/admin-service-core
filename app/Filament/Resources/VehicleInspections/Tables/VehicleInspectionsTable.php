<?php

namespace App\Filament\Resources\VehicleInspections\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class VehicleInspectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inspection_number')
                    ->label('Inspection #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('vehicle.unit_number')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('driver.name')
                    ->label('Driver/Inspector')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('inspection_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pre_trip' => 'Pre-Trip',
                        'post_trip' => 'Post-Trip',
                        'annual' => 'Annual',
                        'dot' => 'DOT',
                        'safety' => 'Safety',
                        'maintenance' => 'Maintenance',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pre_trip', 'post_trip' => 'gray',
                        'annual' => 'info',
                        'dot' => 'warning',
                        'safety' => 'success',
                        'maintenance' => 'primary',
                        default => 'gray',
                    }),
                
                TextColumn::make('inspection_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('inspection_time')
                    ->label('Time')
                    ->time('H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'scheduled',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'warning' => 'needs_repair',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'needs_repair' => 'Needs Repair',
                        default => ucfirst($state),
                    }),
                
                TextColumn::make('odometer_reading')
                    ->label('Odometer')
                    ->numeric()
                    ->suffix(' miles')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('issues_found')
                    ->label('Issues')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) : 0)
                    ->suffix(' issues'),
                
                TextColumn::make('inspector_name')
                    ->label('Inspector')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('next_inspection_date')
                    ->label('Next Due')
                    ->date()
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return null;
                        $daysUntil = Carbon::parse($state)->diffInDays(now(), false);
                        if ($daysUntil > 0) return 'danger';
                        if ($daysUntil > -7) return 'warning';
                        return 'gray';
                    })
                    ->description(function ($state) {
                        if (!$state) return null;
                        $daysUntil = Carbon::parse($state)->diffInDays(now(), false);
                        if ($daysUntil > 0) return 'Overdue by ' . abs($daysUntil) . ' days';
                        if ($daysUntil > -7) return 'Due in ' . abs($daysUntil) . ' days';
                        return 'Due in ' . abs($daysUntil) . ' days';
                    }),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('inspection_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'needs_repair' => 'Needs Repair',
                    ])
                    ->multiple(),
                
                SelectFilter::make('inspection_type')
                    ->label('Type')
                    ->options([
                        'pre_trip' => 'Pre-Trip',
                        'post_trip' => 'Post-Trip',
                        'annual' => 'Annual',
                        'dot' => 'DOT',
                        'safety' => 'Safety',
                        'maintenance' => 'Maintenance',
                    ])
                    ->multiple(),
                
                SelectFilter::make('vehicle_id')
                    ->label('Vehicle')
                    ->relationship('vehicle', 'unit_number')
                    ->searchable()
                    ->preload(),
                
                Filter::make('overdue')
                    ->label('Overdue Inspections')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('next_inspection_date')
                        ->where('next_inspection_date', '<', now())),
                
                Filter::make('upcoming')
                    ->label('Upcoming (7 days)')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('next_inspection_date')
                        ->whereBetween('next_inspection_date', [now(), now()->addDays(7)])),
                
                Filter::make('has_issues')
                    ->label('Has Issues')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('issues_found')
                        ->where('issues_found', '!=', '[]')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No vehicle inspections found')
            ->emptyStateDescription('Start by creating a new vehicle inspection.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }
}