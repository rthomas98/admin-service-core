<?php

namespace App\Filament\Traits;

use Filament\Facades\Filament;

trait FleetManagementResource
{
    /**
     * Fleet management resources should only be visible to transport/disposal companies
     */
    public static function shouldRegisterNavigation(): bool
    {
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return false;
        }

        // Only show for transport, disposal, or companies that do both
        // Hide from customer companies
        return in_array($tenant->type, ['transport', 'disposal', 'both']);
    }
}
