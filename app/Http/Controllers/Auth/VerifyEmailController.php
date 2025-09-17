<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Redirect to Filament admin panel instead of dashboard
            return redirect()->intended('/admin?verified=1');
        }

        $request->fulfill();

        // Redirect to Filament admin panel instead of dashboard
        return redirect()->intended('/admin?verified=1');
    }
}
