<?php

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Create a disposal company
    $this->company = Company::factory()->create([
        'name' => 'Test Disposal Company',
        'type' => 'disposal',
    ]);

    // Create a user for the company
    $this->user = User::factory()->create();

    // Set up Filament panel and tenant
    Filament::setCurrentPanel('admin');
    Filament::setTenant($this->company);

    // Create some customers for testing
    $this->customers = Customer::factory()->count(3)->create([
        'company_id' => $this->company->id,
    ]);
});

it('can render the customers list page', function () {
    actingAs($this->user);

    livewire(ListCustomers::class)
        ->assertSuccessful()
        ->assertSee('Customers');
});

it('can see customers in the table', function () {
    actingAs($this->user);

    livewire(ListCustomers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($this->customers);
});

it('shows customer resource in navigation for disposal companies', function () {
    actingAs($this->user);

    expect(CustomerResource::shouldRegisterNavigation())->toBeTrue();
});

it('can access customer routes', function () {
    actingAs($this->user);

    $response = $this->get('/admin/'.$this->company->id.'/customers');
    $response->assertSuccessful();
});
