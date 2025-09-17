<?php

use App\Enums\ServiceRequestStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\patch;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

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

test('customer can view service requests list', function () {
    ServiceRequest::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
    ]);

    $response = get('/api/customer/service-requests');

    $response->assertSuccessful();
    $response->assertJsonCount(3, 'data');
});

test('customer can create a service request', function () {
    $response = post('/api/customer/service-requests', [
        'title' => 'Need urgent transport',
        'description' => 'Require transport service for equipment',
        'service_type' => 'transport',
        'priority' => 'high',
        'requested_date' => now()->addDays(3)->format('Y-m-d'),
        'preferred_time' => 'morning',
        'location' => '123 Main St',
        'special_instructions' => 'Call before arrival',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'request_number',
            'title',
            'status',
            'priority',
        ],
    ]);

    $this->assertDatabaseHas('service_requests', [
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'title' => 'Need urgent transport',
        'priority' => 'high',
    ]);
});

test('customer can upload attachments to service request', function () {
    $serviceRequest = ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
    ]);

    $file = UploadedFile::fake()->image('test-document.jpg', 1024, 768);

    $response = post("/api/customer/service-requests/{$serviceRequest->id}/attachments", [
        'files' => [$file],
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('service_request_attachments', [
        'service_request_id' => $serviceRequest->id,
        'uploaded_by' => $this->customer->id,
    ]);

    Storage::disk('public')->assertExists('service-requests/'.$serviceRequest->id.'/'.$file->hashName());
});

test('customer cannot upload files larger than 5MB', function () {
    $serviceRequest = ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
    ]);

    $file = UploadedFile::fake()->create('large-file.pdf', 6000); // 6MB

    $response = post("/api/customer/service-requests/{$serviceRequest->id}/attachments", [
        'files' => [$file],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['files.0']);
});

test('customer can add comments to service request', function () {
    $serviceRequest = ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
    ]);

    $response = post("/api/customer/service-requests/{$serviceRequest->id}/comments", [
        'comment' => 'Please update me on the status',
    ]);

    $response->assertSuccessful();

    $this->assertDatabaseHas('service_request_activities', [
        'service_request_id' => $serviceRequest->id,
        'type' => 'comment',
        'description' => 'Please update me on the status',
        'performed_by' => $this->customer->id,
    ]);
});

test('customer can view service request details', function () {
    $serviceRequest = ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'title' => 'Transport Request',
    ]);

    ServiceRequestActivity::create([
        'service_request_id' => $serviceRequest->id,
        'type' => 'status_change',
        'description' => 'Status changed to In Progress',
        'performed_by' => $this->customer->id,
    ]);

    $response = get("/api/customer/service-requests/{$serviceRequest->id}");

    $response->assertSuccessful();
    $response->assertJsonFragment([
        'title' => 'Transport Request',
    ]);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'request_number',
            'title',
            'activities',
            'attachments',
        ],
    ]);
});

test('customer can cancel their own service request', function () {
    $serviceRequest = ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => ServiceRequestStatus::PENDING,
    ]);

    $response = patch("/api/customer/service-requests/{$serviceRequest->id}/cancel");

    $response->assertSuccessful();

    $serviceRequest->refresh();
    expect($serviceRequest->status)->toBe(ServiceRequestStatus::CANCELLED);

    $this->assertDatabaseHas('service_request_activities', [
        'service_request_id' => $serviceRequest->id,
        'type' => 'status_change',
        'performed_by' => $this->customer->id,
    ]);
});

test('customer cannot cancel completed service request', function () {
    $serviceRequest = ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => ServiceRequestStatus::COMPLETED,
    ]);

    $response = patch("/api/customer/service-requests/{$serviceRequest->id}/cancel");

    $response->assertStatus(422);
    $response->assertJsonFragment([
        'message' => 'Cannot cancel a completed service request',
    ]);
});

test('customer cannot view other customers service requests', function () {
    $otherCustomer = Customer::factory()->create([
        'company_id' => $this->company->id,
    ]);

    $serviceRequest = ServiceRequest::factory()->create([
        'customer_id' => $otherCustomer->id,
        'company_id' => $this->company->id,
    ]);

    $response = get("/api/customer/service-requests/{$serviceRequest->id}");

    $response->assertStatus(403);
});

test('service request filters work correctly', function () {
    ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => ServiceRequestStatus::PENDING,
    ]);

    ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => ServiceRequestStatus::IN_PROGRESS,
    ]);

    ServiceRequest::factory()->create([
        'customer_id' => $this->customer->id,
        'company_id' => $this->company->id,
        'status' => ServiceRequestStatus::COMPLETED,
    ]);

    $response = get('/api/customer/service-requests?status=pending');

    $response->assertSuccessful();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonFragment([
        'status' => 'pending',
    ]);
});
