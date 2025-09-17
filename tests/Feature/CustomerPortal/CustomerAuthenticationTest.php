<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create([
        'name' => 'Test Company',
        'slug' => 'test-company',
    ]);
});

test('customer can register with valid invite token', function () {
    $customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    $admin = User::factory()->create();

    $invite = CustomerInvite::create([
        'customer_id' => $customer->id,
        'company_id' => $this->company->id,
        'token' => 'test-token-123',
        'email' => 'john@example.com',
        'invited_by' => $admin->id,
        'expires_at' => now()->addDays(3),
    ]);

    $response = post("/customer-portal/{$this->company->slug}/auth/register/{$invite->token}", [
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect("/customer-portal/{$this->company->slug}/dashboard");

    $customer->refresh();
    expect($customer->portal_access)->toBeTrue();
    expect($customer->email)->toBe('john@example.com');
    expect(Hash::check('Password123!', $customer->portal_password))->toBeTrue();

    $invite->refresh();
    expect($invite->accepted_at)->not->toBeNull();
});

test('customer cannot register with expired invite token', function () {
    $customer = Customer::factory()->create(['company_id' => $this->company->id]);
    $admin = User::factory()->create();

    $invite = CustomerInvite::create([
        'customer_id' => $customer->id,
        'company_id' => $this->company->id,
        'token' => 'expired-token',
        'email' => 'expired@example.com',
        'invited_by' => $admin->id,
        'expires_at' => now()->subDay(),
    ]);

    $response = get("/customer-portal/{$this->company->slug}/auth/register/{$invite->token}");

    $response->assertRedirect("/customer-portal/{$this->company->slug}/auth/invite-expired");
});

test('customer can login with valid credentials', function () {
    $customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'email' => 'customer@example.com',
        'portal_password' => Hash::make('password123'),
        'portal_access' => true,
    ]);

    $response = post("/customer-portal/{$this->company->slug}/auth/login", [
        'email' => 'customer@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect("/customer-portal/{$this->company->slug}/dashboard");
    $this->assertAuthenticatedAs($customer, 'customer');
});

test('customer cannot login with invalid credentials', function () {
    $customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'email' => 'customer@example.com',
        'portal_password' => Hash::make('password123'),
        'portal_access' => true,
    ]);

    $response = post("/customer-portal/{$this->company->slug}/auth/login", [
        'email' => 'customer@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('customer');
});

test('customer without portal access cannot login', function () {
    $customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'email' => 'noaccess@example.com',
        'portal_password' => Hash::make('password123'),
        'portal_access' => false,
    ]);

    $response = post("/customer-portal/{$this->company->slug}/auth/login", [
        'email' => 'noaccess@example.com',
        'password' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('customer');
});

test('authenticated customer can access dashboard', function () {
    $customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'portal_access' => true,
    ]);

    actingAs($customer, 'customer');

    $response = get("/customer-portal/{$this->company->slug}/dashboard");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('customer-portal/dashboard')
        ->has('customer')
        ->has('stats')
    );
});

test('unauthenticated user cannot access dashboard', function () {
    $response = get("/customer-portal/{$this->company->slug}/dashboard");

    $response->assertRedirect("/customer-portal/{$this->company->slug}/auth/login");
});

test('customer can logout', function () {
    $customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'portal_access' => true,
    ]);

    actingAs($customer, 'customer');

    $response = post("/customer-portal/{$this->company->slug}/auth/logout");

    $response->assertRedirect("/customer-portal/{$this->company->slug}/auth/login");
    $this->assertGuest('customer');
});
