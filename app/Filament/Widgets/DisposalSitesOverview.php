<?php

namespace App\Filament\Widgets;

use App\Models\DisposalSite;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class DisposalSitesOverview extends TableWidget
{
    protected static ?string $heading = '🏭 Disposal Sites Status';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];

    protected function getTableQuery(): Builder
    {
        $tenant = Filament::getTenant();

        if (! $tenant || ! $tenant->isRawDisposal()) {
            return DisposalSite::query()->whereRaw('1 = 0');
        }

        return DisposalSite::where('company_id', $tenant->id)
            ->where('status', 'active')
            ->orderByRaw('current_capacity / total_capacity DESC');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Site Name')
                ->searchable()
                ->weight('bold')
                ->icon('heroicon-o-building-office-2')
                ->iconColor('primary'),

            TextColumn::make('location')
                ->label('Location')
                ->description(fn ($record) => $record->parish)
                ->searchable()
                ->icon('heroicon-o-map-pin'),

            TextColumn::make('site_type')
                ->label('Type')
                ->badge()
                ->color(fn (?string $state): string => match ($state) {
                    'landfill' => 'primary',
                    'recycling' => 'success',
                    'composting' => 'warning',
                    'hazardous' => 'danger',
                    'transfer_station' => 'info',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucfirst($state))),

            TextColumn::make('capacity_percentage')
                ->label('Capacity Used')
                ->getStateUsing(fn ($record) => $record->total_capacity > 0
                        ? round(($record->current_capacity / $record->total_capacity) * 100, 1).'%'
                        : '0%'
                )
                ->badge()
                ->color(fn ($state): string => floatval($state) < 60 ? 'success' :
                    (floatval($state) < 85 ? 'warning' : 'danger')
                )
                ->extraAttributes(fn ($state) => [
                    'style' => 'font-weight: bold;',
                ]),

            TextColumn::make('current_capacity')
                ->label('Current / Total')
                ->getStateUsing(fn ($record) => number_format($record->current_capacity).' / '.
                    number_format($record->total_capacity).' tons'
                )
                ->alignEnd(),

            TextColumn::make('daily_intake')
                ->label('Daily Avg')
                ->getStateUsing(fn ($record) => number_format($record->daily_intake_average, 1).' tons/day'
                )
                ->color('gray')
                ->alignEnd(),

            TextColumn::make('days_until_full')
                ->label('Est. Days Left')
                ->getStateUsing(function ($record) {
                    if ($record->daily_intake_average <= 0) {
                        return '∞';
                    }
                    $remainingCapacity = $record->total_capacity - $record->current_capacity;
                    $daysLeft = round($remainingCapacity / $record->daily_intake_average);

                    return $daysLeft > 0 ? $daysLeft : 'Full';
                })
                ->color(fn ($state) => $state === 'Full' ? 'danger' :
                    (is_numeric($state) && $state < 30 ? 'warning' : 'success')
                )
                ->weight('bold')
                ->alignEnd(),

            TextColumn::make('status')
                ->badge()
                ->color(fn (?string $state): string => match ($state) {
                    'active' => 'success',
                    'maintenance' => 'warning',
                    'closed' => 'danger',
                    'inactive' => 'gray',
                    default => 'gray',
                })
                ->icon(fn (?string $state): string => match ($state) {
                    'active' => 'heroicon-o-check-circle',
                    'maintenance' => 'heroicon-o-wrench',
                    'closed' => 'heroicon-o-x-circle',
                    'inactive' => 'heroicon-o-pause-circle',
                    default => 'heroicon-o-pause-circle',
                }),
        ];
    }

    protected function getTablePaginationPageOptions(): array
    {
        return [5, 10];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return '📍 No Active Disposal Sites';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'There are no active disposal sites configured.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-building-office-2';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->striped();
    }
}
