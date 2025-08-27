<?php

namespace App\Enums;

enum TimePeriod: string
{
    case AM = 'AM';
    case PM = 'PM';

    public function label(): string
    {
        return $this->value;
    }
}