<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CustomerWelcomeWidget extends Widget
{
    protected static ?int $sort = 0;

    protected string $view = 'filament.widgets.customer-welcome';

    protected int|string|array $columnSpan = 'full';

    public function getUserName(): string
    {
        return Auth::user()->name ?? 'User';
    }

    public function getCompanyName(): string
    {
        $tenant = Filament::getTenant();

        return $tenant ? $tenant->name : 'Your Company';
    }

    public function getGreeting(): string
    {
        $hour = now()->hour;

        if ($hour < 12) {
            return 'Good morning';
        } elseif ($hour < 17) {
            return 'Good afternoon';
        } else {
            return 'Good evening';
        }
    }

    public function getCurrentDate(): string
    {
        return now()->format('l, F j, Y');
    }

    public function getTips(): array
    {
        return [
            'Use Quick Actions to access frequently used features',
            'Track your invoices and payments in real-time',
            'Invite team members to collaborate in the portal',
            'Generate quotes quickly for your customers',
            'Monitor service requests and their status',
        ];
    }

    public function getRandomTip(): string
    {
        $tips = $this->getTips();

        return $tips[array_rand($tips)];
    }
}
