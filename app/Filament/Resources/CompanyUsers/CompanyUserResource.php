<?php

namespace App\Filament\Resources\CompanyUsers;

use App\Filament\Resources\CompanyUsers\Pages\CreateCompanyUser;
use App\Filament\Resources\CompanyUsers\Pages\EditCompanyUser;
use App\Filament\Resources\CompanyUsers\Pages\ListCompanyUsers;
use App\Filament\Resources\CompanyUsers\Schemas\CompanyUserForm;
use App\Filament\Resources\CompanyUsers\Tables\CompanyUsersTable;
use App\Filament\Traits\HasCompanyBasedVisibility;
use App\Models\CompanyUser;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CompanyUserResource extends Resource
{
    use HasCompanyBasedVisibility;

    protected static ?string $model = CompanyUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Customer Management';

    protected static ?string $navigationLabel = 'Customer Company Owners';

    protected static ?string $modelLabel = 'Company Owner';

    protected static ?string $pluralModelLabel = 'Company Owners';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CompanyUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyUsersTable::configure($table);
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
            'index' => ListCompanyUsers::route('/'),
            'create' => CreateCompanyUser::route('/create'),
            'edit' => EditCompanyUser::route('/{record}/edit'),
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
}
