<?php

namespace App\Filament\Resources\ServiceRequests\RelationManagers;

use App\Models\ServiceRequestAttachment;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Attachments';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file')
                    ->label('File')
                    ->required()
                    ->acceptedFileTypes(['image/*', 'application/pdf', '.doc', '.docx', '.xls', '.xlsx', '.txt'])
                    ->maxSize(10240) // 10MB
                    ->downloadable()
                    ->previewable()
                    ->openable()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Optional description for this file')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_filename')
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Preview')
                    ->circular()
                    ->size(40)
                    ->visibility('private')
                    ->defaultImageUrl(function (ServiceRequestAttachment $record) {
                        return $record->isImage() ? null : asset('images/file-icon.png');
                    }),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Filename')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Filename copied')
                    ->limit(30),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn (int $state): string => $this->formatFileSize($state))
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('mime_type')
                    ->label('Type')
                    ->getStateUsing(function (ServiceRequestAttachment $record): string {
                        $ext = pathinfo($record->original_filename, PATHINFO_EXTENSION);

                        return strtoupper($ext);
                    })
                    ->color(function (ServiceRequestAttachment $record): string {
                        if ($record->isImage()) {
                            return 'success';
                        }
                        if ($record->isPdf()) {
                            return 'warning';
                        }
                        if ($record->isDocument()) {
                            return 'info';
                        }

                        return 'gray';
                    }),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Uploaded by')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable()
                    ->since(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (ServiceRequestAttachment $record): ?string {
                        return $record->description;
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mime_type')
                    ->label('File Type')
                    ->options([
                        'image' => 'Images',
                        'pdf' => 'PDF Documents',
                        'document' => 'Documents',
                        'other' => 'Other',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'image' => $query->where('mime_type', 'like', 'image/%'),
                            'pdf' => $query->where('mime_type', 'application/pdf'),
                            'document' => $query->whereIn('mime_type', [
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/plain',
                            ]),
                            'other' => $query->where('mime_type', 'not like', 'image/%')
                                ->where('mime_type', '!=', 'application/pdf')
                                ->whereNotIn('mime_type', [
                                    'application/msword',
                                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    'application/vnd.ms-excel',
                                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'text/plain',
                                ]),
                            default => $query,
                        };
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Upload File')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = auth()->id();

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (ServiceRequestAttachment $record): string => route('admin.service-requests.attachments.download', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ServiceRequestAttachment $record): string => $record->getFileUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (ServiceRequestAttachment $record): bool => $record->isImage() || $record->isPdf()),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } else {
            return $bytes.' bytes';
        }
    }
}
