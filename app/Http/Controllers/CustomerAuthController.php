<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcceptInviteRequest;
use App\Models\Customer;
use App\Models\CustomerInvite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAuthController extends Controller
{
    /**
     * Show the customer registration form
     */
    public function showRegistrationForm(Request $request, string $token): Response|RedirectResponse
    {
        // Add timing delay to prevent timing attacks
        usleep(random_int(100000, 300000)); // 100-300ms random delay

        // Use timing-safe token lookup
        $invite = CustomerInvite::findByValidToken($token);

        // Check if invitation exists and is valid
        if (! $invite) {
            // Always return same generic message to prevent enumeration
            return redirect()->route('home')->with('error', 'Invalid or expired invitation.');
        }

        // Check if invitation is still valid
        if (! $invite->isValid()) {
            // Always return same generic message
            return redirect()->route('home')->with('error', 'Invalid or expired invitation.');
        }

        // If customer already has portal access, redirect them to login
        if ($invite->customer && $invite->customer->hasPortalAccess()) {
            return redirect()->route('customer.login')
                ->with('info', 'You already have portal access. Please log in with your existing credentials.');
        }

        return Inertia::render('Customer/Register', [
            'invite' => [
                'email' => $invite->email,
                'token' => $invite->token,
                'customer_name' => $invite->customer?->full_name,
                'company_name' => $invite->company->name,
                'expires_at' => $invite->expires_at->format('F j, Y \a\t g:i A'),
            ],
        ]);
    }

    /**
     * Handle customer registration
     */
    public function register(AcceptInviteRequest $request): RedirectResponse
    {
        // Add timing delay to prevent timing attacks
        usleep(random_int(100000, 300000));

        // Get sanitized data
        $data = $request->sanitized();

        // Find the invitation using secure method
        $invite = CustomerInvite::findByValidToken($request->token);

        if (! $invite) {
            // Generic error message to prevent enumeration
            return back()->withErrors(['token' => 'Invalid or expired invitation.']);
        }

        try {
            // Get or create the customer
            $customer = $invite->customer;

            if (! $customer) {
                // Parse name into first and last name
                $nameParts = explode(' ', $data['name'], 2);
                $firstName = $nameParts[0] ?? '';
                $lastName = $nameParts[1] ?? '';

                // Create new customer if not linked
                $customer = Customer::create([
                    'company_id' => $invite->company_id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $request->phone ?? null,
                    'phone_ext' => $request->phone_ext ?? null,
                    'emails' => [$invite->email],
                    'portal_access' => true,
                    'portal_password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]);

                // Update the invite to link to the new customer
                $invite->update(['customer_id' => $customer->id]);
            } else {
                // Parse name into first and last name
                $nameParts = explode(' ', $data['name'], 2);
                $firstName = $nameParts[0] ?? $customer->first_name;
                $lastName = $nameParts[1] ?? $customer->last_name;

                // Update existing customer with registration details
                $customer->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $request->phone ?? $customer->phone,
                    'phone_ext' => $request->phone_ext ?? $customer->phone_ext,
                    'portal_access' => true,
                    'portal_password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]);

                // Ensure the email is in the customer's emails array
                $emails = is_array($customer->emails) ? $customer->emails : json_decode($customer->emails ?? '[]', true);
                if (! in_array($invite->email, $emails)) {
                    $emails[] = $invite->email;
                    $customer->update(['emails' => $emails]);
                }
            }

            // Mark the invitation as accepted
            $invite->markAsAccepted();

            // Log the customer in
            Auth::guard('customer')->login($customer);

            return redirect()->route('customer.dashboard')
                ->with('success', 'Welcome! Your account has been successfully created and you are now logged in.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'There was an error creating your account. Please try again.']);
        }
    }

    /**
     * Show the customer login form
     */
    public function showLoginForm(): Response
    {
        return Inertia::render('Customer/Login');
    }

    /**
     * Handle customer login
     */
    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Find customer by email
        $customer = Customer::whereJsonContains('emails', $request->email)
            ->where('portal_access', true)
            ->first();

        if (! $customer || ! Hash::check($request->password, $customer->portal_password)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ]);
        }

        // Log the customer in
        Auth::guard('customer')->login($customer, $request->boolean('remember'));

        return redirect()->intended(route('customer.dashboard'));
    }

    /**
     * Handle customer logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('info', 'You have been successfully logged out.');
    }

    /**
     * Show the customer dashboard
     */
    public function dashboard(): Response
    {
        $customer = Auth::guard('customer')->user();

        // Load relationships
        $customer->load(['company', 'serviceRequests' => function ($query) {
            $query->latest()->take(5);
        }, 'invoices' => function ($query) {
            $query->latest()->take(5);
        }]);

        // Get recent service requests
        $recentServiceRequests = $customer->serviceRequests;

        // Get recent invoices
        $recentInvoices = $customer->invoices;

        // Get notification count
        $unreadNotifications = \App\Models\Notification::where('recipient_type', \App\Models\Customer::class)
            ->where('recipient_id', $customer->id)
            ->where('read_at', null)
            ->count();

        return Inertia::render('Customer/Dashboard', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->getNotificationEmail(),
                'phone' => $customer->display_phone,
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'zip' => $customer->zip,
                'customer_number' => $customer->customer_number,
                'customer_since' => $customer->customer_since?->format('F Y'),
            ],
            'company' => [
                'name' => $customer->company->name,
                'phone' => $customer->company->phone,
                'email' => $customer->company->email,
            ],
            'recent_service_requests' => $recentServiceRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->title ?? 'Service Request',
                    'status' => $request->status->value,
                    'status_label' => $request->status->getLabel(),
                    'priority' => $request->priority,
                    'created_at' => $request->created_at->format('M j, Y'),
                ];
            }),
            'recent_invoices' => $recentInvoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => number_format($invoice->total_amount, 2),
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date->format('M j, Y'),
                    'is_overdue' => $invoice->isOverdue(),
                ];
            }),
            'stats' => [
                'unread_notifications' => $unreadNotifications,
                'active_service_requests' => $customer->serviceRequests()
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->count(),
                'pending_invoices' => $customer->invoices()
                    ->whereIn('status', ['pending', 'overdue'])
                    ->count(),
            ],
        ]);
    }

    /**
     * Show invoice details for customer
     */
    public function showInvoice(Request $request, Invoice $invoice): Response
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the invoice belongs to the customer
        if ($invoice->customer_id !== $customer->id) {
            abort(403, 'Unauthorized access to invoice');
        }

        // Load invoice with related data
        $invoice->load(['customer', 'company', 'payments']);

        return Inertia::render('Customer/InvoiceDetail', [
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->getNotificationEmail(),
                'organization' => $customer->organization,
            ],
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date->format('M j, Y'),
                'due_date' => $invoice->due_date->format('M j, Y'),
                'status' => $invoice->status,
                'is_overdue' => $invoice->isOverdue(),
                'description' => $invoice->description,
                'notes' => $invoice->notes,
                'line_items' => $invoice->line_items ?? [],
                'subtotal' => number_format($invoice->subtotal, 2),
                'tax_rate' => $invoice->tax_rate,
                'tax_amount' => number_format($invoice->tax_amount, 2),
                'total_amount' => number_format($invoice->total_amount, 2),
                'amount_paid' => number_format($invoice->amount_paid, 2),
                'balance_due' => number_format($invoice->balance_due, 2),
                'billing_address' => $invoice->billing_address,
                'billing_city' => $invoice->billing_city,
                'billing_parish' => $invoice->billing_parish,
                'billing_postal_code' => $invoice->billing_postal_code,
                'company' => [
                    'name' => $invoice->company->name,
                    'address' => $invoice->company->address ?? '',
                    'phone' => $invoice->company->phone ?? '',
                    'email' => $invoice->company->email ?? '',
                ],
                'payments' => $invoice->payments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => number_format($payment->amount, 2),
                        'payment_date' => $payment->payment_date->format('M j, Y'),
                        'payment_method' => $payment->payment_method,
                        'reference_number' => $payment->reference_number,
                    ];
                }),
            ],
        ]);
    }
}
