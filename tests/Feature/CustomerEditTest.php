<?php

use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a test company (RAW Disposal)
    $this->company = Company::create([
        'name' => 'RAW Disposal',
        'slug' => 'raw-disposal',
        'address' => '123 Test St',
        'city' => 'Test City',
        'state' => 'TS',
        'zip' => '12345',
        'phone' => '555-0100',
        'email' => 'test@rawdisposal.com',
    ]);

    // Create a test admin user
    $this->user = User::factory()->create([
        'name' => 'Test Admin',
        'email' => 'admin@test.com',
    ]);

    // Create a test customer
    $this->customer = Customer::create([
        'company_id' => $this->company->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'name' => 'John Doe',
        'emails' => 'john@example.com',
        'phone' => '555-0123',
        'address' => '456 Customer St',
        'city' => 'Customer City',
        'state' => 'CS',
        'zip' => '54321',
    ]);
});

test('customer can be edited through Filament', function () {
    // Authenticate as admin
    $this->actingAs($this->user);

    // Set the tenant context
    Filament::setTenant($this->company);

    // Visit the edit page
    $response = $this->get("/admin/1/customers/{$this->customer->id}/edit");
    $response->assertStatus(200);

    // Test the Livewire component
    Livewire::test(EditCustomer::class, ['record' => $this->customer->id])
        ->assertSet('data.first_name', 'John')
        ->assertSet('data.last_name', 'Doe')
        ->assertSet('data.emails', 'john@example.com')
        ->set('data.first_name', 'Jane')
        ->set('data.phone', '555-9999')
        ->set('data.address', '789 New Address')
        ->call('save')
        ->assertHasNoErrors();

    // Verify the changes were saved
    $this->customer->refresh();
    expect($this->customer->first_name)->toBe('Jane');
    expect($this->customer->phone)->toBe('555-9999');
    expect($this->customer->address)->toBe('789 New Address');
});

test('customer edit respects company tenant isolation', function () {
    // Create another company
    $otherCompany = Company::create([
        'name' => 'Other Company',
        'slug' => 'other-company',
        'address' => '999 Other St',
        'city' => 'Other City',
        'state' => 'OT',
        'zip' => '99999',
        'phone' => '555-9999',
        'email' => 'test@other.com',
    ]);

    // Create a customer for the other company
    $otherCustomer = Customer::create([
        'company_id' => $otherCompany->id,
        'first_name' => 'Other',
        'last_name' => 'Customer',
        'name' => 'Other Customer',
        'emails' => 'other@example.com',
        'phone' => '555-8888',
        'address' => '888 Other St',
        'city' => 'Other City',
        'state' => 'OT',
        'zip' => '88888',
    ]);

    // Authenticate as admin
    $this->actingAs($this->user);

    // Set tenant to RAW Disposal
    Filament::setTenant($this->company);

    // Try to access other company's customer - should fail
    $response = $this->get("/admin/1/customers/{$otherCustomer->id}/edit");
    $response->assertStatus(404);
});

test('customer save validates required fields', function () {
    // Authenticate as admin
    $this->actingAs($this->user);

    // Set the tenant context
    Filament::setTenant($this->company);

    // Test validation with empty required fields
    Livewire::test(EditCustomer::class, ['record' => $this->customer->id])
        ->set('data.emails', '') // Clear a potentially required field
        ->call('save')
        ->assertHasErrors(['data.emails']); // Should have validation error
});
