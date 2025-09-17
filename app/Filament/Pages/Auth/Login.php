<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;

class Login extends BaseLogin
{
    /**
     * Override to provide better error messages for multi-tenancy
     */
    protected function throwFailureValidationException(): never
    {
        $user = \App\Models\User::where('email', $this->data['email'])->first();

        if ($user) {
            // Check if user has companies
            if ($user->companies()->count() === 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'data.email' => __('Your account is not associated with any companies. Please contact your administrator.'),
                ]);
            }
        }

        // Default error
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    /**
     * Override the redirect response after successful login for multi-tenant apps
     */
    protected function getRedirectResponse(): RedirectResponse
    {
        $user = Filament::auth()->user();
        $panel = $this->getPanel();

        // Get the first available tenant for the user
        $tenants = $user->getTenants($panel);

        if ($tenants->count() > 0) {
            $firstTenant = $tenants->first();

            // Generate the URL for the first tenant
            $url = $panel->getUrl($firstTenant);

            if ($url) {
                return redirect($url);
            }
        }

        // Fallback to default behavior
        return parent::getRedirectResponse();
    }
}
