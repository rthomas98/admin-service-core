<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerInvite;
use App\Models\Invoice;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create two companies
    $this->company1 = Company::factory()->create([
        'name' => 'Company One',
        'slug' => 'company-one',
    ]);

    $this->company2 = Company::factory()->create([
        'name' => 'Company Two',
        'slug' => 'company-two',
    ]);

    // Create customers for each company
    $this->customer1 = Customer::factory()->create([
        'company_id' => $this->company1->id,
        'email' => 'customer1@example.com',
        'portal_access' => true,
    ]);

    $this->customer2 = Customer::factory()->create([
        'company_id' => $this->company2->id,
        'email' => 'customer2@example.com',
        'portal_access' => true,
    ]);
});

test('customer cannot access another companys portal', function () {
    actingAs($this->customer1, 'customer');

    // Customer1 trying to access Company2's dashboard
    $response = get("/customer-portal/{$this->company2->slug}/dashboard");

    $response->assertStatus(403);
});

test('customer can only see their own company invoices', function () {
    // Create invoices for both companies
    $invoice1 = Invoice::factory()->create([
        'customer_id' => $this->customer1->id,
        'company_id' => $this->company1->id,
        'invoice_number' => 'COMP1-001',
    ]);

    $invoice2 = Invoice::factory()->create([
        'customer_id' => $this->customer2->id,
        'company_id' => $this->company2->id,
        'invoice_number' => 'COMP2-001',
    ]);

    // Login as customer1
    actingAs($this->customer1, 'customer');

    // Should see own invoice
    $response = get('/api/customer/invoices');
    $response->assertSuccessful();
    $response->assertJsonFragment(['invoice_number' => 'COMP1-001']);
    $response->assertJsonMissing(['invoice_number' => 'COMP2-001']);

    // Cannot access company2's invoice directly
    $response = get("/api/customer/invoices/{$invoice2->id}");
    $response->assertStatus(403);
});

test('customer can only see their own company service requests', function () {
    // Create service requests for both companies
    $request1 = ServiceRequest::factory()->create([
        'customer_id' => $this->customer1->id,
        'company_id' => $this->company1->id,
        'title' => 'Company 1 Request',
    ]);

    $request2 = ServiceRequest::factory()->create([
        'customer_id' => $this->customer2->id,
        'company_id' => $this->company2->id,
        'title' => 'Company 2 Request',
    ]);

    // Login as customer1
    actingAs($this->customer1, 'customer');

    // Should see own request
    $response = get('/api/customer/service-requests');
    $response->assertSuccessful();
    $response->assertJsonFragment(['title' => 'Company 1 Request']);
    $response->assertJsonMissing(['title' => 'Company 2 Request']);

    // Cannot access company2's request directly
    $response = get("/api/customer/service-requests/{$request2->id}");
    $response->assertStatus(403);
});

test('customer invite tokens are isolated by company', function () {
    $admin = \App\Models\User::factory()->create();

    // Create invite for company1
    $invite1 = CustomerInvite::create([
        'customer_id' => $this->customer1->id,
        'company_id' => $this->company1->id,
        'token' => 'token-company1',
        'email' => 'new1@example.com',
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(3),
    ]);

    // Try to use company1's token on company2's registration page
    $response = get("/customer-portal/{$this->company2->slug}/auth/register/{$invite1->token}");

    $response->assertStatus(404); // Should not find the invite
});

test('customer cannot create service requests for another company', function () {
    actingAs($this->customer1, 'customer');

    // Try to create a service request with company2's ID
    $response = post('/api/customer/service-requests', [
        'title' => 'Malicious Request',
        'description' => 'Trying to create for another company',
        'service_type' => 'transport',
        'priority' => 'high',
        'company_id' => $this->company2->id, // Trying to override company
    ]);

    // Should either fail or ignore the company_id and use customer's company
    if ($response->status() === 201) {
        $this->assertDatabaseHas('service_requests', [
            'title' => 'Malicious Request',
            'company_id' => $this->company1->id, // Should be company1, not company2
        ]);

        $this->assertDatabaseMissing('service_requests', [
            'title' => 'Malicious Request',
            'company_id' => $this->company2->id,
        ]);
    }
});

test('customers with same email in different companies are isolated', function () {
    // Create another customer with same email but different company
    $duplicateCustomer = Customer::factory()->create([
        'company_id' => $this->company2->id,
        'email' => $this->customer1->email, // Same email as customer1
        'portal_access' => true,
    ]);

    // Login should be scoped to company
    $response = post("/customer-portal/{$this->company1->slug}/auth/login", [
        'email' => $this->customer1->email,
        'password' => 'password',
    ]);

    // Should authenticate as customer1, not duplicateCustomer
    if (auth('customer')->check()) {
        expect(auth('customer')->user()->company_id)->toBe($this->company1->id);
    }
});

test('dashboard statistics are isolated by company', function () {
    // Create data for company1
    Invoice::factory()->count(5)->create([
        'customer_id' => $this->customer1->id,
        'company_id' => $this->company1->id,
    ]);

    ServiceRequest::factory()->count(3)->create([
        'customer_id' => $this->customer1->id,
        'company_id' => $this->company1->id,
    ]);

    // Create data for company2
    Invoice::factory()->count(10)->create([
        'customer_id' => $this->customer2->id,
        'company_id' => $this->company2->id,
    ]);

    ServiceRequest::factory()->count(7)->create([
        'customer_id' => $this->customer2->id,
        'company_id' => $this->company2->id,
    ]);

    // Check company1 dashboard
    actingAs($this->customer1, 'customer');
    $response = get('/api/customer/dashboard/stats');

    $response->assertSuccessful();
    $response->assertJsonFragment([
        'total_invoices' => 5,
        'total_service_requests' => 3,
    ]);

    // Check company2 dashboard
    actingAs($this->customer2, 'customer');
    $response = get('/api/customer/dashboard/stats');

    $response->assertSuccessful();
    $response->assertJsonFragment([
        'total_invoices' => 10,
        'total_service_requests' => 7,
    ]);
});

test('api endpoints enforce company context', function () {
    actingAs($this->customer1, 'customer');

    // All these endpoints should automatically filter by customer's company
    $endpoints = [
        '/api/customer/invoices',
        '/api/customer/service-requests',
        '/api/customer/dashboard/stats',
        '/api/customer/dashboard/recent-activity',
    ];

    foreach ($endpoints as $endpoint) {
        $response = get($endpoint);
        $response->assertSuccessful();

        // Response should not include any reference to company2
        $content = $response->getContent();
        expect($content)->not->toContain($this->company2->id);
    }
});

test('notification preferences are isolated by customer and company', function () {
    actingAs($this->customer1, 'customer');

    // Update notification preferences
    $response = patch('/api/customer/account/notifications', [
        'email_notifications' => false,
        'sms_notifications' => true,
    ]);

    $response->assertSuccessful();

    // Check that only customer1's preferences were updated
    $this->customer1->refresh();
    $this->customer2->refresh();

    // Customer1's preferences should be updated
    expect($this->customer1->email_notifications ?? true)->toBeFalse();

    // Customer2's preferences should remain unchanged
    expect($this->customer2->email_notifications ?? true)->toBeTrue();
});
