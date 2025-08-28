<?php

namespace Filament\Actions;

use Filament\Actions\Action;

class EditAction extends Action
{
    public static function make(?string $name = 'edit'): static
    {
        return parent::make($name)
            ->label('Edit')
            ->icon('heroicon-o-pencil')
            ->color('primary');
    }
}