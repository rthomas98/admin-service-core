<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class OverdueInvoicesAlert extends TableWidget
{
    protected static ?string $heading = '🚨 Overdue Invoices - Action Required';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): Builder
    {
        $tenant = Filament::getTenant();
        
        if (!$tenant) {
            return Invoice::query()->whereRaw('1 = 0');
        }
        
        return Invoice::where('company_id', $tenant->id)
            ->whereIn('status', ['sent', 'partially_paid'])
            ->where('due_date', '<', Carbon::today())
            ->with(['customer'])
            ->orderByRaw("CASE 
                WHEN due_date < '" . Carbon::now()->subDays(90)->format('Y-m-d') . "' THEN 1
                WHEN due_date < '" . Carbon::now()->subDays(60)->format('Y-m-d') . "' THEN 2
                WHEN due_date < '" . Carbon::now()->subDays(30)->format('Y-m-d') . "' THEN 3
                ELSE 4
            END")
            ->orderBy('balance_due', 'desc');
    }
    
    protected function getTableColumns(): array
    {
        return [
            BadgeColumn::make('days_overdue')
                ->label('Status')
                ->getStateUsing(fn ($record) => 
                    Carbon::parse($record->due_date)->diffInDays(now()) . ' days'
                )
                ->color(fn ($record) => 
                    Carbon::parse($record->due_date)->diffInDays(now()) > 60 ? 'danger' : 
                    (Carbon::parse($record->due_date)->diffInDays(now()) > 30 ? 'warning' : 'gray')
                )
                ->icon(fn ($record) => 
                    Carbon::parse($record->due_date)->diffInDays(now()) > 60 
                        ? 'heroicon-o-exclamation-triangle' 
                        : 'heroicon-o-clock'
                )
                ->extraAttributes(fn ($record) => 
                    Carbon::parse($record->due_date)->diffInDays(now()) > 90 
                        ? ['class' => 'animate-pulse'] 
                        : []
                ),
                
            TextColumn::make('invoice_number')
                ->label('Invoice #')
                ->searchable()
                ->weight('bold')
                ->copyable()
                ->copyMessage('Invoice number copied')
                ->url(fn ($record) => '/admin/' . $record->company_id . '/invoices/' . $record->id),
                
            TextColumn::make('customer.name')
                ->label('Customer')
                ->searchable()
                ->weight('bold')
                ->limit(25)
                ->tooltip(fn ($record) => $record->customer->name ?? 'N/A'),
                
            TextColumn::make('customer.phone')
                ->label('Contact')
                ->icon('heroicon-o-phone')
                ->iconColor('primary')
                ->copyable()
                ->placeholder('No phone'),
                
            TextColumn::make('invoice_date')
                ->label('Invoice Date')
                ->date('M j, Y')
                ->color('gray'),
                
            TextColumn::make('due_date')
                ->label('Was Due')
                ->date('M j, Y')
                ->color('danger')
                ->weight('bold'),
                
            TextColumn::make('balance_due')
                ->label('Outstanding Amount')
                ->money('USD')
                ->alignEnd()
                ->color('danger')
                ->weight('bold')
                ->size('lg'),
                
            TextColumn::make('total_amount')
                ->label('Original Total')
                ->money('USD')
                ->alignEnd()
                ->color('gray'),
                
            BadgeColumn::make('follow_up_status')
                ->label('Action')
                ->getStateUsing(function ($record) {
                    $daysOverdue = Carbon::parse($record->due_date)->diffInDays(now());
                    if ($daysOverdue > 90) return 'Send to Collections';
                    if ($daysOverdue > 60) return 'Final Notice';
                    if ($daysOverdue > 30) return '2nd Reminder';
                    if ($daysOverdue > 14) return '1st Reminder';
                    return 'Send Reminder';
                })
                ->color(function ($state) {
                    return match($state) {
                        'Send to Collections' => 'danger',
                        'Final Notice' => 'danger',
                        '2nd Reminder' => 'warning',
                        '1st Reminder' => 'warning',
                        default => 'info'
                    };
                })
                ->icon('heroicon-o-envelope'),
        ];
    }
    
    protected function getTablePaginationPageOptions(): array
    {
        return [10, 25];
    }
    
    protected function getTableEmptyStateHeading(): ?string
    {
        return '🎉 No Overdue Invoices';
    }
    
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'All invoices are either paid or within their due date.';
    }
    
    protected function getTableEmptyStateIcon(): ?string
    {
        return 'heroicon-o-check-circle';
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([])
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->striped();
    }
}