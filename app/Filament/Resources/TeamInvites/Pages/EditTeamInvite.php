<?php

namespace App\Filament\Resources\TeamInvites\Pages;

use App\Filament\Resources\TeamInvites\TeamInviteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTeamInvite extends EditRecord
{
    protected static string $resource = TeamInviteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
