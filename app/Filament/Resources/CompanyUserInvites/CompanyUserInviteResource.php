<?php

namespace App\Filament\Resources\CompanyUserInvites;

use App\Filament\Resources\CompanyUserInvites\Pages\CreateCompanyUserInvite;
use App\Filament\Resources\CompanyUserInvites\Pages\EditCompanyUserInvite;
use App\Filament\Resources\CompanyUserInvites\Pages\ListCompanyUserInvites;
use App\Filament\Resources\CompanyUserInvites\Schemas\CompanyUserInviteForm;
use App\Filament\Resources\CompanyUserInvites\Tables\CompanyUserInvitesTable;
use App\Models\CompanyUserInvite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CompanyUserInviteResource extends Resource
{
    protected static ?string $model = CompanyUserInvite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?string $navigationLabel = 'Company Owner Invites';

    protected static ?string $modelLabel = 'Company Owner Invite';

    protected static ?string $pluralModelLabel = 'Company Owner Invites';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CompanyUserInviteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyUserInvitesTable::configure($table);
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
            'index' => ListCompanyUserInvites::route('/'),
            'create' => CreateCompanyUserInvite::route('/create'),
            'edit' => EditCompanyUserInvite::route('/{record}/edit'),
        ];
    }
}
