<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerSetupController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // Check if user has company role for any company
        $company = $user->companies()->wherePivot('role', 'company')->first();

        if (! $company) {
            return redirect('/admin');
        }

        // Check if a customer already exists for this user
        $customer = Customer::where('company_id', $company->id)
            ->whereJsonContains('emails', $user->email)
            ->first();

        // If customer exists and is fully set up, redirect to admin
        if ($customer && $customer->organization) {
            return redirect('/admin');
        }

        return Inertia::render('Customer/Setup', [
            'serviceProvider' => [
                'id' => $company->id,
                'name' => $company->name,
                'slug' => $company->slug,
                'type' => $company->type,
            ],
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'customer' => $customer ? [
                'id' => $customer->id,
                'organization' => $customer->organization,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'zip' => $customer->zip,
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if user has company role for any company
        $company = $user->companies()->wherePivot('role', 'company')->first();

        if (! $company) {
            return redirect('/admin');
        }

        $validated = $request->validate([
            'organization' => 'required|string|max:255',
            'business_type' => 'required|string|max:100',
            'tax_exemption_details' => 'nullable|string|max:255',
            'tax_exempt_reason' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'phone_ext' => 'nullable|string|max:10',
            'secondary_phone' => 'nullable|string|max:20',
            'secondary_phone_ext' => 'nullable|string|max:10',
            'fax' => 'nullable|string|max:20',
            'fax_ext' => 'nullable|string|max:10',
            'address' => 'required|string|max:255',
            'secondary_address' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'zip' => 'required|string|max:20',
            'county' => 'nullable|string|max:100',
            'delivery_method' => 'nullable|string|max:50',
            'referral' => 'nullable|string|max:255',
            'internal_memo' => 'nullable|string|max:1000',
        ]);

        // Check if customer already exists
        $customer = Customer::where('company_id', $company->id)
            ->whereJsonContains('emails', $user->email)
            ->first();

        $customerData = array_merge($validated, [
            'company_id' => $company->id,
            'name' => $user->name,
            'emails' => [$user->email],
            'portal_access' => true,
            'notifications_enabled' => true,
            'preferred_notification_method' => 'email',
        ]);

        if ($customer) {
            // Update existing customer, don't overwrite customer_since
            $customer->update($customerData);
        } else {
            // Create new customer with customer_since
            $customerData['customer_since'] = now();
            Customer::create($customerData);
        }

        return redirect('/admin')->with('success', 'Customer profile setup completed successfully!');
    }
}
