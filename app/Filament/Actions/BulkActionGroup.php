<?php

namespace App\Filament\Actions;

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
