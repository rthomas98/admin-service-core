<?php

namespace App\Filament\Resources\TeamInvites\Pages;

use App\Filament\Resources\TeamInvites\TeamInviteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTeamInvites extends ListRecords
{
    protected static string $resource = TeamInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
