<?php

namespace App\Filament\Resources\ServiceSchedules\Tables;

use App\Filament\Resources\ServiceSchedules\ServiceScheduleResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('equipment.id')
                    ->searchable(),
                TextColumn::make('technician.name')
                    ->searchable(),
                TextColumn::make('service_type')
                    ->searchable(),
                TextColumn::make('scheduled_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_datetime')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('priority')
                    ->searchable(),
                TextColumn::make('service_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('materials_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estimated_duration_minutes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('actual_duration_minutes')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('requires_followup')
                    ->boolean(),
                TextColumn::make('followup_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn ($record) => ServiceScheduleResource::getUrl('view', ['record' => $record])
            );
    }
}
