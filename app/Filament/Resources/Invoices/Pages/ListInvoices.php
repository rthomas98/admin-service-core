<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    
    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\InvoicePaymentStats::class,
        ];
    }
    
    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\PendingInvoicesTable::class,
            \App\Filament\Widgets\OverdueInvoicesAlert::class,
        ];
    }
}
