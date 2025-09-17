<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

class DeleteBulkAction extends Action
{
    public static function make(?string $name = 'delete'): static
    {
        return parent::make($name)
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation();
    }
}
