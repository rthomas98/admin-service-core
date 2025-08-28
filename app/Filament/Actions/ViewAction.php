<?php

namespace Filament\Actions;

use Filament\Actions\Action;

class ViewAction extends Action
{
    public static function make(?string $name = 'view'): static
    {
        return parent::make($name)
            ->label('View')
            ->icon('heroicon-o-eye')
            ->color('gray');
    }
}