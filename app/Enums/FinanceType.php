<?php

namespace App\Enums;

enum FinanceType: string
{
    case Loan = 'loan';
    case Lease = 'lease';
    case Rental = 'rental';

    public function label(): string
    {
        return match ($this) {
            self::Loan => 'Loan',
            self::Lease => 'Lease',
            self::Rental => 'Rental',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Loan => 'primary',
            self::Lease => 'success',
            self::Rental => 'warning',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Loan => 'heroicon-o-credit-card',
            self::Lease => 'heroicon-o-document-text',
            self::Rental => 'heroicon-o-clock',
        };
    }
}
