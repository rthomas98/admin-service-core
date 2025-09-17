<?php

namespace App\Filament\Resources\TeamInvites;

use App\Filament\Resources\TeamInvites\Pages\CreateTeamInvite;
use App\Filament\Resources\TeamInvites\Pages\EditTeamInvite;
use App\Filament\Resources\TeamInvites\Pages\ListTeamInvites;
use App\Filament\Resources\TeamInvites\Schemas\TeamInviteForm;
use App\Filament\Resources\TeamInvites\Tables\TeamInvitesTable;
use App\Models\TeamInvite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TeamInviteResource extends Resource
{
    protected static ?string $model = TeamInvite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static bool $isScopedToTenant = false;

    protected static string|UnitEnum|null $navigationGroup = 'User Management';

    protected static ?string $navigationLabel = 'Team Invitations';

    protected static ?string $modelLabel = 'Team Invitation';

    protected static ?string $pluralModelLabel = 'Team Invitations';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return TeamInviteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamInvitesTable::configure($table);
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
            'index' => ListTeamInvites::route('/'),
            'create' => CreateTeamInvite::route('/create'),
            'edit' => EditTeamInvite::route('/{record}/edit'),
        ];
    }
}
