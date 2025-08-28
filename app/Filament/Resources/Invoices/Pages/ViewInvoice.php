<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Schemas\InvoiceForm;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\HtmlString;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->form(function ($record) {
                    return InvoiceForm::configure(\Filament\Schemas\Schema::make())
                        ->getComponents();
                })
                ->fillForm(fn ($record) => $record->toArray())
                ->action(function (array $data, $record) {
                    $record->update($data);
                })
                ->modalHeading('Edit Invoice')
                ->modalSubmitActionLabel('Save changes')
                ->modalCancelActionLabel('Cancel')
                ->slideOver()
                ->modalWidth(Width::SevenExtraLarge),
            DeleteAction::make(),
        ];
    }
    
    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Invoice Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'sent' => 'warning',
                                        'paid' => 'success',
                                        'overdue' => 'danger',
                                        'cancelled' => 'gray',
                                        default => 'gray',
                                    }),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('invoice_date')
                                    ->label('Invoice Date')
                                    ->date(),
                                TextEntry::make('due_date')
                                    ->label('Due Date')
                                    ->date(),
                                TextEntry::make('paid_date')
                                    ->label('Paid Date')
                                    ->date()
                                    ->placeholder('Not paid'),
                            ]),
                    ]),
                    
                Section::make('Line Items')
                    ->schema([
                        RepeatableEntry::make('line_items')
                            ->label('')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('type')
                                            ->label('Type')
                                            ->badge()
                                            ->color('info')
                                            ->formatStateUsing(fn ($state) => match($state) {
                                                'equipment' => 'Equipment Rental',
                                                'service' => 'Service',
                                                'product' => 'Product',
                                                'disposal' => 'Disposal Fee',
                                                'delivery' => 'Delivery',
                                                'other' => 'Other',
                                                default => ucfirst($state ?? 'N/A')
                                            }),
                                        TextEntry::make('description')
                                            ->label('Description')
                                            ->columnSpan(2),
                                        TextEntry::make('quantity')
                                            ->label('Quantity'),
                                        TextEntry::make('unit_price')
                                            ->label('Unit Price')
                                            ->money('USD')
                                            ->formatStateUsing(fn ($state) => '$' . number_format(floatval($state ?? 0), 2)),
                                        TextEntry::make('total')
                                            ->label('Total')
                                            ->weight('bold')
                                            ->formatStateUsing(function ($record) {
                                                $qty = floatval($record['quantity'] ?? 0);
                                                $price = floatval($record['unit_price'] ?? 0);
                                                return '$' . number_format($qty * $price, 2);
                                            }),
                                    ]),
                            ])
                            ->contained(false),
                    ]),
                    
                Section::make('Financial Summary')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('USD'),
                                TextEntry::make('tax_amount')
                                    ->label('Tax Amount')
                                    ->money('USD'),
                                TextEntry::make('discount_amount')
                                    ->label('Discount')
                                    ->money('USD')
                                    ->default('$0.00'),
                                TextEntry::make('total_amount')
                                    ->label('Total Amount')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->size('lg'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('amount_paid')
                                    ->label('Amount Paid')
                                    ->money('USD'),
                                TextEntry::make('balance_due')
                                    ->label('Balance Due')
                                    ->money('USD')
                                    ->weight('bold')
                                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                            ]),
                    ]),
                    
                Section::make('Billing Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('billing_address')
                                    ->label('Street Address')
                                    ->placeholder('No address'),
                                TextEntry::make('full_billing_address')
                                    ->label('Complete Address')
                                    ->formatStateUsing(function ($record) {
                                        $parts = array_filter([
                                            $record->billing_address,
                                            $record->billing_city,
                                            $record->billing_parish,
                                            $record->billing_postal_code,
                                        ]);
                                        return implode(', ', $parts) ?: 'No address';
                                    }),
                            ]),
                    ])
                    ->collapsible(),
                    
                Section::make('Additional Information')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Internal Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                        TextEntry::make('terms_conditions')
                            ->label('Terms & Conditions')
                            ->placeholder('No terms')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}