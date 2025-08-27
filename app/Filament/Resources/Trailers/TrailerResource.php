<?php

namespace App\Filament\Resources\Trailers;

use App\Filament\Resources\Trailers\Pages\CreateTrailer;
use App\Filament\Resources\Trailers\Pages\EditTrailer;
use App\Filament\Resources\Trailers\Pages\ListTrailers;
use App\Filament\Resources\Trailers\Pages\ViewTrailer;
use App\Filament\Resources\Trailers\Schemas\TrailerForm;
use App\Filament\Resources\Trailers\Tables\TrailersTable;
use App\Models\Trailer;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

class TrailerResource extends Resource
{
    protected static ?string $model = Trailer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;
    
    protected static string | UnitEnum | null $navigationGroup = 'Fleet Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return TrailerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrailersTable::configure($table);
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
            'index' => ListTrailers::route('/'),
            'create' => CreateTrailer::route('/create'),
            'view' => ViewTrailer::route('/{record}'),
            'edit' => EditTrailer::route('/{record}/edit'),
        ];
    }
    
    public static function canViewAny(): bool
    {
        $tenant = Filament::getTenant();
        
        // Only show for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canCreate(): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow creation for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canEdit(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow editing for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
    
    public static function canDelete(Model $record): bool
    {
        $tenant = Filament::getTenant();
        
        // Only allow deletion for LIV Transport company
        return $tenant && $tenant->isLivTransport();
    }
}
