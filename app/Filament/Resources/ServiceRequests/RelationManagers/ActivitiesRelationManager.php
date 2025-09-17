<?php

namespace App\Filament\Resources\ServiceRequests\RelationManagers;

use App\Models\ServiceRequestActivity;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Activity Timeline';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('activity_type')
                    ->label('Activity Type')
                    ->options([
                        ServiceRequestActivity::TYPE_COMMENT => 'Comment',
                        ServiceRequestActivity::TYPE_STATUS_CHANGED => 'Status Changed',
                        ServiceRequestActivity::TYPE_ASSIGNED => 'Assigned',
                        ServiceRequestActivity::TYPE_UNASSIGNED => 'Unassigned',
                        ServiceRequestActivity::TYPE_SCHEDULED => 'Scheduled',
                        ServiceRequestActivity::TYPE_COST_UPDATED => 'Cost Updated',
                        ServiceRequestActivity::TYPE_PRIORITY_CHANGED => 'Priority Changed',
                        ServiceRequestActivity::TYPE_UPDATED => 'Updated',
                    ])
                    ->required()
                    ->default(ServiceRequestActivity::TYPE_COMMENT),

                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_internal')
                    ->label('Internal Note')
                    ->helperText('Internal notes are only visible to staff members')
                    ->default(false),

                Forms\Components\DateTimePicker::make('performed_at')
                    ->label('Performed At')
                    ->default(now())
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\IconColumn::make('activity_type')
                    ->label('')
                    ->icon(fn (ServiceRequestActivity $record): string => $record->getActivityIcon())
                    ->color(fn (ServiceRequestActivity $record): string => $record->getActivityColor())
                    ->size('lg'),

                Tables\Columns\TextColumn::make('title')
                    ->label('Activity')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(100)
                    ->tooltip(function (ServiceRequestActivity $record): ?string {
                        return $record->description;
                    })
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('activity_type')
                    ->label('Type')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            ServiceRequestActivity::TYPE_CREATED => 'Created',
                            ServiceRequestActivity::TYPE_STATUS_CHANGED => 'Status Changed',
                            ServiceRequestActivity::TYPE_ASSIGNED => 'Assigned',
                            ServiceRequestActivity::TYPE_UNASSIGNED => 'Unassigned',
                            ServiceRequestActivity::TYPE_COMMENT => 'Comment',
                            ServiceRequestActivity::TYPE_ATTACHMENT_ADDED => 'File Added',
                            ServiceRequestActivity::TYPE_ATTACHMENT_REMOVED => 'File Removed',
                            ServiceRequestActivity::TYPE_SCHEDULED => 'Scheduled',
                            ServiceRequestActivity::TYPE_COST_UPDATED => 'Cost Updated',
                            ServiceRequestActivity::TYPE_PRIORITY_CHANGED => 'Priority Changed',
                            ServiceRequestActivity::TYPE_CATEGORY_CHANGED => 'Category Changed',
                            ServiceRequestActivity::TYPE_UPDATED => 'Updated',
                            default => ucfirst(str_replace('_', ' ', $state)),
                        };
                    })
                    ->color(fn (ServiceRequestActivity $record): string => $record->getActivityColor()),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_internal')
                    ->label('Internal')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye-slash')
                    ->falseIcon('heroicon-o-eye')
                    ->trueColor('warning')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('performed_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->label('Activity Type')
                    ->options([
                        ServiceRequestActivity::TYPE_CREATED => 'Created',
                        ServiceRequestActivity::TYPE_STATUS_CHANGED => 'Status Changed',
                        ServiceRequestActivity::TYPE_ASSIGNED => 'Assigned',
                        ServiceRequestActivity::TYPE_UNASSIGNED => 'Unassigned',
                        ServiceRequestActivity::TYPE_COMMENT => 'Comment',
                        ServiceRequestActivity::TYPE_ATTACHMENT_ADDED => 'File Added',
                        ServiceRequestActivity::TYPE_ATTACHMENT_REMOVED => 'File Removed',
                        ServiceRequestActivity::TYPE_SCHEDULED => 'Scheduled',
                        ServiceRequestActivity::TYPE_COST_UPDATED => 'Cost Updated',
                        ServiceRequestActivity::TYPE_PRIORITY_CHANGED => 'Priority Changed',
                        ServiceRequestActivity::TYPE_CATEGORY_CHANGED => 'Category Changed',
                        ServiceRequestActivity::TYPE_UPDATED => 'Updated',
                    ]),

                Tables\Filters\TernaryFilter::make('is_internal')
                    ->label('Visibility')
                    ->trueLabel('Internal Only')
                    ->falseLabel('Public')
                    ->native(false),

                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('performed_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('performed_at', '<=', $date),
                            );
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Note')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (ServiceRequestActivity $record): bool => $record->activity_type === ServiceRequestActivity::TYPE_COMMENT
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (ServiceRequestActivity $record): bool => $record->activity_type === ServiceRequestActivity::TYPE_COMMENT
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(function (): bool {
                            return auth()->user()->can('delete_service_request_activities');
                        }),
                ]),
            ])
            ->defaultSort('performed_at', 'desc')
            ->poll('30s'); // Refresh every 30 seconds for real-time updates
    }
}
