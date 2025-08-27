<?php

namespace App\Filament\Resources\Invoices;

use App\Filament\Resources\Invoices\Pages\CreateInvoice;
use App\Filament\Resources\Invoices\Pages\EditInvoice;
use App\Filament\Resources\Invoices\Pages\ListInvoices;
use App\Filament\Resources\Invoices\Pages\ViewInvoice;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use App\Filament\Resources\Invoices\Tables\InvoicesTable;
use App\Models\Invoice;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Invoices';
    
    protected static string | UnitEnum | null $navigationGroup = 'Financial';
    
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return InvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvoices::route('/'),
            'create' => CreateInvoice::route('/create'),
            'view' => ViewInvoice::route('/{record}'),
            'edit' => EditInvoice::route('/{record}/edit'),
        ];
    }
    
    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\InvoicePaymentStats::class,
            \App\Filament\Widgets\PendingInvoicesTable::class,
            \App\Filament\Widgets\OverdueInvoicesAlert::class,
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        $tenant = Filament::getTenant();
        
        if ($tenant) {
            $query->where('company_id', $tenant->id);
        }
        
        return $query;
    }
    
    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();
        
        // Only show for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
    
    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow creation for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
    
    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow editing for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
    
    public static function canDelete(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow deletion for RAW Disposal company
        return $tenant && $tenant->isRawDisposal();
    }
}
