<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyOnboarding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Check if this is a Filament admin route and user has company owner role
            if ($request->is('admin/*') || $request->is('admin')) {
                $companyUser = $user->companies()
                    ->wherePivot('role', 'company')
                    ->first();

                // For company role users, check if they have completed customer setup
                if ($companyUser && $companyUser->pivot->role === 'company') {
                    // Check if customer exists and is set up
                    $customer = \App\Models\Customer::where('company_id', $companyUser->id)
                        ->whereJsonContains('emails', $user->email)
                        ->first();

                    if (! $customer || ! $customer->organization) {
                        // Allow access to the setup route itself to prevent redirect loop
                        if (! $request->routeIs('customer.setup.*')) {
                            return redirect()->route('customer.setup');
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
