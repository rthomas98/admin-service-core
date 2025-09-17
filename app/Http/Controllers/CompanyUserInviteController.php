<?php

namespace App\Http\Controllers;

use App\Mail\CompanyUserInviteEmail;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\CompanyUserInvite;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CompanyUserInviteController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'role' => 'required|in:admin,company,manager,staff,viewer',
            'permissions' => 'nullable|array',
        ]);

        $existingUser = CompanyUser::where('company_id', $validated['company_id'])
            ->where('email', $validated['email'])
            ->first();

        if ($existingUser) {
            return back()->withErrors(['email' => 'This email is already registered for this company.']);
        }

        $existingInvite = CompanyUserInvite::where('company_id', $validated['company_id'])
            ->where('email', $validated['email'])
            ->where('accepted_at', null)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvite) {
            return back()->withErrors(['email' => 'An invitation has already been sent to this email.']);
        }

        $invite = CompanyUserInvite::create([
            'company_id' => $validated['company_id'],
            'email' => $validated['email'],
            'name' => $validated['name'],
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? [],
            'invited_by' => auth()->id(),
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invite->email)->send(new CompanyUserInviteEmail($invite));

        return back()->with('success', 'Invitation sent successfully.');
    }

    public function show(string $token)
    {
        $invite = CompanyUserInvite::where('token', $token)
            ->with('company')
            ->firstOrFail();

        if ($invite->isAccepted()) {
            return redirect('/login')->with('error', 'This invitation has already been accepted.');
        }

        if ($invite->isExpired()) {
            return redirect('/login')->with('error', 'This invitation has expired.');
        }

        return Inertia::render('auth/AcceptInvite', [
            'invite' => [
                'token' => $invite->token,
                'email' => $invite->email,
                'name' => $invite->name,
                'company' => $invite->company->name,
                'role' => $invite->role,
            ],
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $invite = CompanyUserInvite::where('token', $token)->firstOrFail();

        if (! $invite->canBeAccepted()) {
            return redirect('/login')->with('error', 'This invitation cannot be accepted.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = null;
        $isCompanyOwner = false;

        DB::transaction(function () use ($invite, $validated, &$user, &$isCompanyOwner) {
            // Create a regular User for Filament admin access
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $invite->email,
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            // Check if this is a company owner role
            $isCompanyOwner = $invite->role === 'company';

            // Attach user to the service provider company with specified role
            $invite->company->users()->attach($user->id, [
                'role' => $invite->role,
            ]);

            // Create CompanyUser record for company association
            CompanyUser::create([
                'company_id' => $invite->company_id,
                'email' => $invite->email,
                'name' => $validated['name'],
                'password' => Hash::make($validated['password']),
                'role' => $invite->role,
                'permissions' => $invite->permissions,
                'is_active' => true,
                'portal_access' => true,
                'email_verified_at' => now(),
            ]);

            // If this is a company owner, create or prepare Customer record
            if ($isCompanyOwner) {
                // Check if customer already exists
                $customer = Customer::where('company_id', $invite->company_id)
                    ->whereJsonContains('emails', $invite->email)
                    ->first();

                if (! $customer) {
                    // Create basic customer record that will be completed in setup
                    Customer::create([
                        'company_id' => $invite->company_id,
                        'name' => $validated['name'],
                        'emails' => [$invite->email],
                        'portal_access' => true,
                        'customer_since' => now(),
                        'notifications_enabled' => true,
                        'preferred_notification_method' => 'email',
                    ]);
                }
            }

            $invite->markAsAccepted();
        });

        // Log in as the regular user for Filament access
        Auth::login($user);

        // If company owner, redirect to customer setup
        if ($isCompanyOwner) {
            return redirect()->route('customer.setup')->with('success', 'Welcome! Please complete your customer profile setup.');
        }

        // Otherwise redirect to Filament admin panel
        return redirect('/admin')->with('success', 'Account created successfully.');
    }

    public function resend(Request $request)
    {
        $validated = $request->validate([
            'invite_id' => 'required|exists:company_user_invites,id',
        ]);

        $invite = CompanyUserInvite::findOrFail($validated['invite_id']);

        if ($invite->isAccepted()) {
            return back()->withErrors(['invite' => 'This invitation has already been accepted.']);
        }

        $invite->update([
            'token' => Str::random(32),
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invite->email)->send(new CompanyUserInviteEmail($invite));

        return back()->with('success', 'Invitation resent successfully.');
    }

    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'invite_id' => 'required|exists:company_user_invites,id',
        ]);

        $invite = CompanyUserInvite::findOrFail($validated['invite_id']);

        if ($invite->isAccepted()) {
            return back()->withErrors(['invite' => 'This invitation has already been accepted and cannot be canceled.']);
        }

        $invite->delete();

        return back()->with('success', 'Invitation canceled successfully.');
    }
}
