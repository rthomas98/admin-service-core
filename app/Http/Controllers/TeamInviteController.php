<?php

namespace App\Http\Controllers;

use App\Models\TeamInvite;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class TeamInviteController extends Controller
{
    /**
     * Display the registration form for invited team members.
     */
    public function showRegistrationForm(string $token)
    {
        $invite = TeamInvite::where('token', $token)->first();

        if (! $invite) {
            return redirect()->route('login')
                ->with('error', 'Invalid invitation link.');
        }

        if ($invite->isExpired()) {
            return redirect()->route('login')
                ->with('error', 'This invitation has expired. Please contact your administrator for a new invitation.');
        }

        if ($invite->isAccepted()) {
            return redirect()->route('login')
                ->with('info', 'This invitation has already been used. Please log in with your credentials.');
        }

        return Inertia::render('Auth/TeamRegister', [
            'invite' => [
                'token' => $invite->token,
                'email' => $invite->email,
                'name' => $invite->name,
                'role' => $invite->getRoleDescription(),
                'company' => $invite->company?->name,
                'invitedBy' => $invite->invitedBy->name,
                'expiresAt' => $invite->expires_at->toISOString(),
            ],
        ]);
    }

    /**
     * Process the registration form submission.
     */
    public function register(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $invite = TeamInvite::where('token', $request->token)->first();

        if (! $invite || ! $invite->isValid()) {
            return back()->withErrors([
                'token' => 'This invitation is no longer valid.',
            ]);
        }

        // Use database transaction to ensure all operations succeed
        $user = DB::transaction(function () use ($invite, $request) {
            // Check if user already exists with this email
            $user = User::where('email', $invite->email)->first();

            if ($user) {
                // User already exists, just update name and password
                $user->update([
                    'name' => $request->name,
                    'password' => Hash::make($request->password),
                ]);

                // Ensure the role exists before assigning
                $this->ensureRoleExists($invite->role);

                // Assign role if not already assigned
                if (! $user->hasRole($invite->role)) {
                    $user->assignRole($invite->role);
                    Log::info("Assigned role '{$invite->role}' to existing user: {$user->email}");
                }

                // Attach to company if specified
                if ($invite->company_id && ! $user->companies->contains($invite->company_id)) {
                    $user->companies()->attach($invite->company_id, ['role' => $invite->role]);
                    Log::info("Attached user {$user->email} to company ID: {$invite->company_id}");
                }

                // Add any additional permissions
                if ($invite->permissions && is_array($invite->permissions)) {
                    foreach ($invite->permissions as $permission) {
                        if (! $user->hasPermissionTo($permission)) {
                            $user->givePermissionTo($permission);
                        }
                    }
                }
            } else {
                // Create new user
                $user = User::create([
                    'name' => $request->name,
                    'email' => $invite->email,
                    'password' => Hash::make($request->password),
                    'email_verified_at' => now(), // Auto-verify since they came from an invitation
                ]);

                // Ensure the role exists before assigning
                $this->ensureRoleExists($invite->role);

                // Assign role
                $user->assignRole($invite->role);
                Log::info("Created new user {$user->email} with role: {$invite->role}");

                // Attach to company if specified
                if ($invite->company_id) {
                    $user->companies()->attach($invite->company_id, ['role' => $invite->role]);
                    Log::info("Attached new user {$user->email} to company ID: {$invite->company_id}");
                }

                // Add any additional permissions
                if ($invite->permissions && is_array($invite->permissions)) {
                    foreach ($invite->permissions as $permission) {
                        $user->givePermissionTo($permission);
                    }
                }

                event(new Registered($user));
            }

            // Mark invitation as accepted
            $invite->markAsAccepted();

            return $user;
        });

        // Log the user in
        Auth::login($user);

        // Get the correct redirect URL based on tenant
        $redirectUrl = $this->getRedirectUrl($user, $invite);

        return redirect($redirectUrl)
            ->with('success', 'Welcome to the team! Your account has been created successfully.');
    }

    /**
     * Ensure the role exists in the database.
     */
    protected function ensureRoleExists(string $roleName): void
    {
        // Check if role exists, if not create it
        if (! Role::where('name', $roleName)->exists()) {
            Role::create(['name' => $roleName]);
            Log::info("Created missing role: {$roleName}");
        }
    }

    /**
     * Get the redirect URL after successful registration.
     */
    protected function getRedirectUrl(User $user, TeamInvite $invite): string
    {
        // Try to get the admin panel
        try {
            $panel = Filament::getPanel('admin');

            // Get user's tenants
            $tenants = $user->getTenants($panel);

            if ($tenants->count() > 0) {
                // Prefer the company from the invitation if it exists
                if ($invite->company_id) {
                    $tenant = $tenants->firstWhere('id', $invite->company_id);
                    if ($tenant) {
                        return $panel->getUrl($tenant);
                    }
                }

                // Otherwise use the first available tenant
                $firstTenant = $tenants->first();

                return $panel->getUrl($firstTenant);
            }
        } catch (\Exception $e) {
            Log::error('Error getting redirect URL after team registration: '.$e->getMessage());
        }

        // Fallback to login page if no tenant available
        return route('login');
    }
}
