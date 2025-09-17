<?php

namespace App\Filament\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PendingInvoicesTable extends TableWidget
{
    protected static ?string $heading = '⏳ Pending Invoices';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 3,
    ];

    protected function getTableQuery(): Builder
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return Invoice::query()->whereRaw('1 = 0');
        }

        return Invoice::where('company_id', $tenant->id)
            ->whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::PartiallyPaid])
            ->where('due_date', '>=', Carbon::today())
            ->with(['customer'])
            ->orderBy('due_date', 'asc');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('invoice_number')
                ->label('Invoice #')
                ->searchable()
                ->weight('bold')
                ->copyable()
                ->copyMessage('Invoice number copied'),

            TextColumn::make('customer.name')
                ->label('Customer')
                ->searchable()
                ->limit(20)
                ->tooltip(fn ($record) => $record->customer->name ?? 'N/A'),

            TextColumn::make('invoice_date')
                ->label('Date')
                ->date('M j, Y')
                ->sortable(),

            TextColumn::make('due_date')
                ->label('Due Date')
                ->date('M j, Y')
                ->sortable()
                ->color(fn ($record) => Carbon::parse($record->due_date)->diffInDays(now()) <= 3
                        ? 'warning'
                        : 'gray'
                )
                ->description(fn ($record) => 'Due in '.Carbon::parse($record->due_date)->diffForHumans(null, true)
                ),

            TextColumn::make('total_amount')
                ->label('Total')
                ->money('USD')
                ->alignEnd()
                ->weight('bold'),

            TextColumn::make('balance_due')
                ->label('Balance Due')
                ->money('USD')
                ->alignEnd()
                ->color('warning')
                ->weight('bold'),

            TextColumn::make('payment_progress')
                ->label('Paid')
                ->badge()
                ->getStateUsing(fn ($record) => $record->total_amount > 0
                        ? round((($record->total_amount - $record->balance_due) / $record->total_amount) * 100).'%'
                        : '0%'
                )
                ->color(fn ($state) => intval($state) >= 75 ? 'success' :
                    (intval($state) >= 50 ? 'warning' : 'gray')
                ),

            TextColumn::make('status')
                ->badge()
                ->color(fn (InvoiceStatus $state): string => match ($state) {
                    InvoiceStatus::Sent => 'warning',
                    InvoiceStatus::PartiallyPaid => 'info',
                    default => 'gray',
                })
                ->formatStateUsing(fn (InvoiceStatus $state): string => $state->getLabel()
                ),
        ];
    }

    protected function getTablePaginationPageOptions(): array
    {
        return [5, 10];
    }

    protected function getTableEmptyStateHeading(): ?string
    {
        return '✅ No Pending Invoices';
    }

    protected function getTableEmptyStateDescription(): ?string
    {
        return 'All sent invoices have been paid or are overdue.';
    }

    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-document-check';
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
