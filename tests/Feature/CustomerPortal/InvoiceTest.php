<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create([
        'name' => 'Test Company',
        'slug' => 'test-company',
    ]);

    $this->customer = Customer::factory()->create([
        'company_id' => $this->company->id,
        'portal_access' => true,
    ]);

    actingAs($this->customer, 'customer');
});

test('customer can view their invoices list', function () {
    Invoice::factory()->count(5)->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
    ]);

    $response = get('/api/customer/invoices');

    $response->assertSuccessful();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'invoice_number',
                'invoice_date',
                'due_date',
                'total_amount',
                'status',
                'balance_due',
            ],
        ],
        'links',
        'meta',
    ]);
});

test('customer can view invoice details', function () {
    $invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 1500.00,
        'status' => 'sent',
    ]);

    $response = get("/api/customer/invoices/{$invoice->id}");

    $response->assertSuccessful();
    $response->assertJsonFragment([
        'invoice_number' => 'INV-2024-001',
        'total_amount' => 1500.00,
        'status' => 'sent',
    ]);
});

test('customer cannot view other customers invoices', function () {
    $otherCustomer = Customer::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $invoice = Invoice::factory()->create([
        'customer_id' => $otherCustomer->id,
        'company_id' => $this->company->id,
    ]);

    $response = get("/api/customer/invoices/{$invoice->id}");

    $response->assertStatus(403);
});

test('customer can download invoice PDF', function () {
    $invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
    ]);

    $response = get("/api/customer/invoices/{$invoice->id}/download");

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertHeader('Content-Disposition');
});

test('customer can view invoice payments', function () {
    $invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'total_amount' => 1000.00,
    ]);

    Payment::factory()->count(2)->create([
        'invoice_id' => $invoice->id,
        'amount' => 250.00,
    ]);

    $response = get("/api/customer/invoices/{$invoice->id}/payments");

    $response->assertSuccessful();
    $response->assertJsonCount(2, 'data');
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'amount',
                'payment_date',
                'payment_method',
                'reference_number',
            ],
        ],
    ]);
});

test('invoice status filter works correctly', function () {
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => 'paid',
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => 'sent',
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => 'overdue',
    ]);

    $response = get('/api/customer/invoices?status=paid');

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonFragment([
        'status' => 'paid',
    ]);
});

test('invoice search functionality works', function () {
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'invoice_number' => 'INV-2024-SEARCH',
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'invoice_number' => 'INV-2024-OTHER',
    ]);

    $response = get('/api/customer/invoices?search=SEARCH');

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonFragment([
        'invoice_number' => 'INV-2024-SEARCH',
    ]);
});

test('invoice date range filter works', function () {
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'invoice_date' => now()->subDays(10),
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'invoice_date' => now()->subDays(5),
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'invoice_date' => now()->addDays(5),
    ]);

    $startDate = now()->subDays(7)->format('Y-m-d');
    $endDate = now()->format('Y-m-d');

    $response = get("/api/customer/invoices?start_date={$startDate}&end_date={$endDate}");

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data');
});

test('invoice pagination works correctly', function () {
    Invoice::factory()->count(25)->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
    ]);

    $response = get('/api/customer/invoices?per_page=10&page=1');

    $response->assertSuccessful();
    $response->assertJsonCount(10, 'data');
    $response->assertJsonPath('meta.total', 25);
    $response->assertJsonPath('meta.per_page', 10);
    $response->assertJsonPath('meta.current_page', 1);

    $response = get('/api/customer/invoices?per_page=10&page=3');

    $response->assertSuccessful();
    $response->assertJsonCount(5, 'data');
    $response->assertJsonPath('meta.current_page', 3);
});
