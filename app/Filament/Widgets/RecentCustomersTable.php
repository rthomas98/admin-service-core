<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentCustomersTable extends TableWidget
{
    protected static ?string $heading = '👥 Recent Customers';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 15];
    }

    public function getDefaultTableRecordsPerPageSelectOption(): int
    {
        return 5;
    }

    protected function getTableQuery(): Builder
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return Customer::query()->whereRaw('1 = 0');
        }

        return Customer::where('company_id', $tenant->id)
            ->latest()
            ->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Customer Name')
                ->searchable()
                ->sortable()
                ->weight('bold')
                ->limit(30),

            TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->sortable()
                ->copyable()
                ->copyMessage('Email copied')
                ->icon('heroicon-m-envelope')
                ->iconColor('gray'),

            TextColumn::make('phone')
                ->label('Phone')
                ->searchable()
                ->formatStateUsing(fn ($state) => $state ?: '—')
                ->icon('heroicon-m-phone')
                ->iconColor('gray'),

            TextColumn::make('business_type')
                ->label('Type')
                ->badge()
                ->color(fn (?string $state): string => match ($state) {
                    'Commercial' => 'success',
                    'Residential' => 'info',
                    'Government' => 'warning',
                    default => 'gray',
                })
                ->formatStateUsing(fn ($state) => $state ?: 'Unknown'),

            TextColumn::make('created_at')
                ->label('Added')
                ->dateTime('M j, Y')
                ->sortable()
                ->description(fn ($record) => $record->created_at->diffForHumans()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label('View')
                ->icon('heroicon-m-eye')
                ->url(fn ($record) => '/admin/'.Filament::getTenant()->id.'/customers/'.$record->id)
                ->openUrlInNewTab(),

            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-m-pencil-square')
                ->url(fn ($record) => '/admin/'.Filament::getTenant()->id.'/customers/'.$record->id.'/edit'),
        ];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No customers yet';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Start by adding your first customer to manage your business relationships.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('create')
                ->label('Add First Customer')
                ->url('/admin/'.Filament::getTenant()->id.'/customers/create')
                ->icon('heroicon-m-plus')
                ->button(),
        ];
    }
}
