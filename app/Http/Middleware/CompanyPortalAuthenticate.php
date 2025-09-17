<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CompanyPortalAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('company')->check()) {
            $companySlug = $request->route('companySlug');

            if ($companySlug) {
                return redirect("/company-portal/{$companySlug}/login");
            }

            return redirect('/login');
        }

        $user = Auth::guard('company')->user();
        $companySlug = $request->route('companySlug');

        if ($companySlug) {
            $company = Company::where('slug', $companySlug)->first();

            if (! $company || $user->company_id !== $company->id) {
                Auth::guard('company')->logout();

                return redirect("/company-portal/{$companySlug}/login")
                    ->with('error', 'Unauthorized access to this company portal.');
            }
        }

        if (! $user->canAccessPortal()) {
            Auth::guard('company')->logout();

            return redirect('/login')
                ->with('error', 'Your account is inactive or does not have portal access.');
        }

        return $next($request);
    }
}
