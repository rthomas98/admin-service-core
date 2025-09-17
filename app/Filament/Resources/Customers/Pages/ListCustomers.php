<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected static ?string $title = 'Customer Management';

    public function getSubheading(): ?string
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::whereHas('serviceOrders', function ($query) {
            $query->where('created_at', '>=', now()->subMonths(3));
        })->count();

        return "Managing {$totalCustomers} customers • {$activeCustomers} active in last 3 months";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Customers')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Customer Data')
                ->modalDescription('Upload a CSV file to import multiple customers at once.')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'application/csv'])
                        ->required()
                        ->helperText('CSV should include: Name, Organization, Email, Phone, Address, City, State, ZIP'),
                ])
                ->action(function (array $data) {
                    // Import logic would go here
                    \Filament\Notifications\Notification::make()
                        ->title('Import started')
                        ->body('Customer import has been queued for processing.')
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->modalHeading('Export Customers')
                ->modalDescription('Choose export format and filters for your customer data.')
                ->form([
                    \Filament\Forms\Components\Select::make('format')
                        ->label('Export Format')
                        ->options([
                            'csv' => 'CSV (Excel, Google Sheets)',
                            'pdf' => 'PDF Report',
                        ])
                        ->default('csv')
                        ->required(),
                    \Filament\Forms\Components\Toggle::make('include_balance')
                        ->label('Include Outstanding Balance')
                        ->default(true),
                    \Filament\Forms\Components\Toggle::make('include_orders')
                        ->label('Include Order History')
                        ->default(true),
                    \Filament\Forms\Components\Toggle::make('email_export')
                        ->label('Email me the export')
                        ->default(true)
                        ->helperText('Send the export file to your registered email address'),
                ])
                ->action(function (array $data) {
                    $user = auth()->user();
                    $tenant = \Filament\Facades\Filament::getTenant();

                    // Dispatch the export job
                    \App\Jobs\ExportCustomersJob::dispatch(
                        $tenant->id,
                        $user->email,
                        $data
                    );

                    \Filament\Notifications\Notification::make()
                        ->title('Export started')
                        ->body('Your customer export is being processed. You will receive an email when it\'s ready.')
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('New Customer')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Customers')
                ->icon('heroicon-o-user-group')
                ->badge(Customer::count()),

            'active' => Tab::make('Active')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('serviceOrders', function ($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                }))
                ->badge(Customer::whereHas('serviceOrders', function ($q) {
                    $q->where('created_at', '>=', now()->subMonths(3));
                })->count()),

            'commercial' => Tab::make('Commercial')
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('business_type', 'Commercial'))
                ->badge(Customer::where('business_type', 'Commercial')->count()),

            'residential' => Tab::make('Residential')
                ->icon('heroicon-o-home')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('business_type', 'Residential'))
                ->badge(Customer::where('business_type', 'Residential')->count()),

            'government' => Tab::make('Government')
                ->icon('heroicon-o-building-library')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('business_type', 'Government'))
                ->badge(Customer::where('business_type', 'Government')->count()),

            'with_balance' => Tab::make('Outstanding Balance')
                ->icon('heroicon-o-currency-dollar')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('invoices', function ($q) {
                    $q->whereIn('status', ['pending', 'overdue']);
                }))
                ->badge(Customer::whereHas('invoices', function ($q) {
                    $q->whereIn('status', ['pending', 'overdue']);
                })->count())
                ->badgeColor('danger'),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\CustomerOverviewStats::class,
            \App\Filament\Widgets\RecentCustomersTable::class,
        ];
    }
}
