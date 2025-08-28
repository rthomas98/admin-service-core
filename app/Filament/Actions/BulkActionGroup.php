<?php

namespace Filament\Actions;

use Filament\Actions\Action;

class BulkActionGroup
{
    protected array $actions = [];
    
    public function __construct(array $actions)
    {
        $this->actions = $actions;
    }
    
    public static function make(array $actions): static
    {
        return new static($actions);
    }
    
    public function getActions(): array
    {
        return $this->actions;
    }
}