<?php

use App\Models\Company;
use App\Models\CompanyUserInvite;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('company owner is redirected to setup when customer profile is not complete', function () {
    $company = Company::factory()->create([
        'name' => 'RAW Disposal LLC',
        'slug' => 'raw-disposal',
    ]);

    $user = User::factory()->create();
    $company->users()->attach($user->id, ['role' => 'company']);

    // Create customer record without organization (incomplete profile)
    Customer::create([
        'company_id' => $company->id,
        'name' => $user->name,
        'emails' => [$user->email],
        'portal_access' => true,
        'customer_since' => now(),
        'notifications_enabled' => true,
        'preferred_notification_method' => 'email',
    ]);

    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirect('/customer/setup');
});

test('company owner can access setup page', function () {
    $company = Company::factory()->create([
        'name' => 'RAW Disposal LLC',
        'slug' => 'raw-disposal',
    ]);

    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
    $company->users()->attach($user->id, ['role' => 'company']);

    // Create customer record without organization (incomplete profile)
    Customer::create([
        'company_id' => $company->id,
        'name' => $user->name,
        'emails' => [$user->email],
        'portal_access' => true,
        'customer_since' => now(),
        'notifications_enabled' => true,
        'preferred_notification_method' => 'email',
    ]);

    $this->actingAs($user)
        ->get('/customer/setup')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Customer/Setup')
            ->has('serviceProvider')
            ->has('user')
            ->where('serviceProvider.name', 'RAW Disposal LLC')
            ->where('user.email', 'john@example.com')
        );
});

test('company owner can complete customer setup', function () {
    $company = Company::factory()->create([
        'name' => 'RAW Disposal LLC',
        'slug' => 'raw-disposal',
    ]);

    $user = User::factory()->create();
    $company->users()->attach($user->id, ['role' => 'company']);

    // Create customer record without organization (incomplete profile)
    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => $user->name,
        'emails' => [$user->email],
        'portal_access' => true,
        'customer_since' => now(),
        'notifications_enabled' => true,
        'preferred_notification_method' => 'email',
    ]);

    $this->actingAs($user)
        ->post('/customer/setup', [
            'organization' => 'Acme Construction Inc',
            'business_type' => 'Corporation',
            'tax_exemption_details' => null,
            'tax_exempt_reason' => null,
            'phone' => '(555) 123-4567',
            'phone_ext' => null,
            'secondary_phone' => null,
            'secondary_phone_ext' => null,
            'fax' => null,
            'fax_ext' => null,
            'address' => '123 Main St',
            'secondary_address' => null,
            'city' => 'Houston',
            'state' => 'TX',
            'zip' => '77001',
            'county' => 'Harris',
            'delivery_method' => 'Standard',
            'referral' => 'Google',
            'internal_memo' => 'New customer from online invitation',
        ])
        ->assertRedirect('/admin')
        ->assertSessionHas('success');

    $customer->refresh();
    expect($customer->organization)->toBe('Acme Construction Inc');
    expect($customer->business_type)->toBe('Corporation');
    expect($customer->phone)->toBe('(555) 123-4567');
    expect($customer->address)->toBe('123 Main St');
    expect($customer->city)->toBe('Houston');
    expect($customer->state)->toBe('TX');
    expect($customer->zip)->toBe('77001');
});

test('regular users are not redirected to setup', function () {
    $company = Company::factory()->create([
        'name' => 'RAW Disposal LLC',
        'slug' => 'raw-disposal',
    ]);

    $user = User::factory()->create();
    $company->users()->attach($user->id, ['role' => 'admin']);

    // Filament redirects /admin to /admin/{companyId} due to tenancy
    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirect("/admin/{$company->id}");

    // Then accessing the company-specific admin should work
    $this->actingAs($user)
        ->get("/admin/{$company->id}")
        ->assertOk();
});

test('company owner cannot access setup if already completed', function () {
    $company = Company::factory()->create([
        'name' => 'RAW Disposal LLC',
        'slug' => 'raw-disposal',
    ]);

    $user = User::factory()->create();
    $company->users()->attach($user->id, ['role' => 'company']);

    // Create complete customer record with organization
    Customer::create([
        'company_id' => $company->id,
        'name' => $user->name,
        'emails' => [$user->email],
        'organization' => 'Existing Company',
        'business_type' => 'Corporation',
        'phone' => '555-1234',
        'address' => '123 Main St',
        'city' => 'Houston',
        'state' => 'TX',
        'zip' => '77001',
        'portal_access' => true,
        'customer_since' => now(),
        'notifications_enabled' => true,
        'preferred_notification_method' => 'email',
    ]);

    $this->actingAs($user)
        ->get('/customer/setup')
        ->assertRedirect('/admin');
});

test('accepting company role invitation creates customer record and redirects to setup', function () {
    $serviceProvider = Company::factory()->create([
        'name' => 'RAW Disposal LLC',
        'slug' => 'raw-disposal',
    ]);

    $invite = CompanyUserInvite::factory()->create([
        'company_id' => $serviceProvider->id,
        'role' => 'company',
        'email' => 'newowner@example.com',
    ]);

    $this->post("/company-portal/accept-invite/{$invite->token}", [
        'name' => 'New Owner',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect('/customer/setup')
        ->assertSessionHas('success', 'Welcome! Please complete your customer profile setup.');

    // User should be created
    $this->assertDatabaseHas('users', [
        'email' => 'newowner@example.com',
        'name' => 'New Owner',
    ]);

    // Customer record should be created
    $this->assertDatabaseHas('customers', [
        'company_id' => $serviceProvider->id,
        'name' => 'New Owner',
    ]);

    // Check that emails array contains the email (using raw query for JSON column)
    $customer = Customer::where('company_id', $serviceProvider->id)
        ->where('name', 'New Owner')
        ->first();

    expect($customer)->not->toBeNull();
    expect($customer->emails)->toContain('newowner@example.com');
    expect($customer->portal_access)->toBeTrue();
    expect($customer->organization)->toBeNull(); // Not set yet, needs setup

    // User should be attached to the service provider company with company role
    $user = User::where('email', 'newowner@example.com')->first();
    expect($serviceProvider->users->contains($user))->toBeTrue();

    $pivot = $serviceProvider->users()->where('user_id', $user->id)->first()->pivot;
    expect($pivot->role)->toBe('company');
});

test('customer setup preserves existing customer data when updating', function () {
    $company = Company::factory()->create([
        'name' => 'LIV Transport',
        'slug' => 'liv-transport',
    ]);

    $user = User::factory()->create();
    $company->users()->attach($user->id, ['role' => 'company']);

    // Create customer record with partial data
    $customer = Customer::create([
        'company_id' => $company->id,
        'name' => $user->name,
        'emails' => [$user->email],
        'portal_access' => true,
        'customer_since' => now()->subDays(30),
        'notifications_enabled' => true,
        'preferred_notification_method' => 'email',
        'internal_memo' => 'VIP customer',
    ]);

    $originalCustomerSince = $customer->customer_since;

    $this->actingAs($user)
        ->post('/customer/setup', [
            'organization' => 'Transport Services LLC',
            'business_type' => 'LLC',
            'tax_exemption_details' => null,
            'tax_exempt_reason' => null,
            'phone' => '(555) 999-8888',
            'phone_ext' => null,
            'secondary_phone' => null,
            'secondary_phone_ext' => null,
            'fax' => null,
            'fax_ext' => null,
            'address' => '789 Transport Ave',
            'secondary_address' => null,
            'city' => 'Dallas',
            'state' => 'TX',
            'zip' => '75201',
            'county' => 'Dallas',
            'delivery_method' => 'Express',
            'referral' => 'Partner',
            'internal_memo' => 'VIP customer - Updated profile',
        ])
        ->assertRedirect('/admin')
        ->assertSessionHas('success');

    $customer->refresh();

    // Check that new data was saved
    expect($customer->organization)->toBe('Transport Services LLC');
    expect($customer->business_type)->toBe('LLC');
    expect($customer->phone)->toBe('(555) 999-8888');
    expect($customer->address)->toBe('789 Transport Ave');
    expect($customer->city)->toBe('Dallas');

    // Check that existing data was preserved
    expect($customer->customer_since->format('Y-m-d'))->toBe($originalCustomerSince->format('Y-m-d'));
    expect($customer->portal_access)->toBeTrue();
    expect($customer->internal_memo)->toBe('VIP customer - Updated profile');
});
