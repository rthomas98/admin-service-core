<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

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
}