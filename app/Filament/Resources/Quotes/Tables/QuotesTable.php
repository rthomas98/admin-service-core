<?php

namespace App\Filament\Resources\Quotes\Tables;

use App\Mail\QuoteResponse;
use App\Filament\Resources\Quotes\QuoteResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class QuotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quote_number')
                    ->label('Quote #')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                    
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'warning',
                        'pending' => 'info',
                        'sent' => 'success',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'new' => 'New Request',
                        'pending' => 'Pending Review',
                        'sent' => 'Sent to Customer',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                        default => ucfirst($state),
                    }),
                    
                TextColumn::make('name')
                    ->label('Customer')
                    ->description(fn ($record) => $record->company)
                    ->searchable(['name', 'company']),
                    
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),
                    
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),
                    
                TextColumn::make('project_type')
                    ->label('Type')
                    ->badge()
                    ->color('primary'),
                    
                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->limit(20),
                    
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                    
                TextColumn::make('quote_date')
                    ->label('Submitted')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->description(fn ($record) => $record->quote_date->diffForHumans()),
                    
                TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->valid_until < now() ? 'danger' : 'gray'),
            ])
            ->defaultSort('quote_date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => 'New Request',
                        'pending' => 'Pending Review',
                        'sent' => 'Sent to Customer',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('project_type')
                    ->options([
                        'Construction' => 'Construction',
                        'Event' => 'Event',
                        'Commercial' => 'Commercial',
                        'Residential' => 'Residential',
                        'Industrial' => 'Industrial',
                        'Government/Municipal' => 'Government/Municipal',
                        'Emergency Response' => 'Emergency Response',
                    ]),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn ($record) => QuoteResource::getUrl('edit', ['record' => $record, 'tenant' => $record->company_id ?? 1])),
                    
                Action::make('send')
                    ->label('Send Quote')
                    ->icon('heroicon-m-envelope')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Send Quote to Customer')
                    ->modalDescription(fn ($record) => "Are you sure you want to send this quote to {$record->name} ({$record->email})?")
                    ->modalSubmitActionLabel('Send Quote')
                    ->visible(fn ($record) => $record->total_amount > 0 && in_array($record->status, ['pending', 'new']))
                    ->action(function ($record) {
                        try {
                            // Send the quote email
                            Mail::to($record->email)->send(new QuoteResponse($record));
                            
                            // Update the status to sent
                            $record->update([
                                'status' => 'sent',
                                'sent_date' => now(),
                            ]);
                            
                            Notification::make()
                                ->title('Quote Sent Successfully')
                                ->body("Quote #{$record->quote_number} has been sent to {$record->email}")
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to Send Quote')
                                ->body('An error occurred while sending the quote: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
