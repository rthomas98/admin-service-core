<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CompanyUserAuthController extends Controller
{
    public function showLoginForm(string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        return Inertia::render('CompanyPortal/Login', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
            ],
        ]);
    }

    public function login(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean',
        ]);

        $user = CompanyUser::where('company_id', $company->id)
            ->where('email', $validated['email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->canAccessPortal()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive or does not have portal access.'],
            ]);
        }

        Auth::guard('company')->login($user, $validated['remember'] ?? false);

        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();

        return redirect("/company-portal/{$companySlug}/dashboard");
    }

    public function logout(Request $request, string $companySlug)
    {
        Auth::guard('company')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/company-portal/{$companySlug}/login");
    }

    public function showDashboard(string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();
        $user = Auth::guard('company')->user();

        if ($user->company_id !== $company->id) {
            abort(403, 'Unauthorized access to this company portal.');
        }

        return Inertia::render('CompanyPortal/Dashboard', [
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }
}
