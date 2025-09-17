<?php

use App\Enums\InvoiceStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

// Get RAW Disposal company
$company = Company::where('slug', 'raw-disposal')->first();

if (! $company) {
    echo "RAW Disposal company not found!\n";
    exit;
}

echo "Creating test customer for RAW Disposal...\n";

// Create a test customer with email
$customer = Customer::create([
    'company_id' => $company->id,
    'first_name' => 'Test',
    'last_name' => 'Customer',
    'organization' => 'Test Dumpster Rentals LLC',
    'emails' => ['test@example.com', 'billing@testdumpster.com'],
    'phone' => '512-555-1234',
    'address' => '123 Test Street',
    'city' => 'Austin',
    'state' => 'TX',
    'zip' => '78701',
    'business_type' => 'construction',
    'customer_since' => Carbon::now(),
    'portal_access' => true,
    'portal_password' => bcrypt('password123'),
    'notifications_enabled' => true,
    'preferred_notification_method' => 'email',
]);

echo "Customer created: {$customer->full_name} (ID: {$customer->id})\n";
echo 'Customer email: '.$customer->getNotificationEmail()."\n\n";

echo "Creating test invoice...\n";

// Create a test invoice
$invoice = Invoice::create([
    'company_id' => $company->id,
    'customer_id' => $customer->id,
    'invoice_number' => 'INV-TEST-'.date('Ymd').'-001',
    'invoice_date' => Carbon::now(),
    'due_date' => Carbon::now()->addDays(30),
    'status' => InvoiceStatus::Draft,
    'subtotal' => 850.00,
    'tax_rate' => 8.25,
    'tax_amount' => 70.13,
    'total_amount' => 920.13,
    'balance_due' => 920.13,
    'amount_paid' => 0,
    'line_items' => json_encode([
        [
            'description' => '20 Yard Dumpster Rental - 7 days',
            'quantity' => 1,
            'unit_price' => 75.00,
            'amount' => 525.00,
            'notes' => '7 days @ $75/day',
        ],
        [
            'description' => 'Standard Portable Toilet - 7 days',
            'quantity' => 2,
            'unit_price' => 25.00,
            'amount' => 175.00,
            'notes' => '7 days @ $25/day x 2 units',
        ],
        [
            'description' => 'Delivery & Pickup',
            'quantity' => 1,
            'unit_price' => 150.00,
            'amount' => 150.00,
        ],
    ]),
    'payment_terms' => 'net30',
    'notes' => 'Thank you for your business!',
]);

echo "Invoice created: {$invoice->invoice_number} (ID: {$invoice->id})\n";
echo "Invoice status: {$invoice->status->getLabel()}\n\n";

echo "Now changing invoice status to 'Sent' to trigger notifications...\n";

// Change status to Sent - this should trigger notifications
$invoice->update(['status' => InvoiceStatus::Sent]);

echo "\n✅ Invoice status changed to: {$invoice->status->getLabel()}\n";
echo "\nThe InvoiceObserver should have triggered:\n";
echo "1. Email notification to customer (check logs)\n";
echo "2. In-app notification created in database\n\n";

// Check if notification was created
$notification = \App\Models\Notification::where('recipient_type', 'App\Models\Customer')
    ->where('recipient_id', $customer->id)
    ->where('category', 'invoice')
    ->latest()
    ->first();

if ($notification) {
    echo "✅ In-app notification created successfully!\n";
    echo "   Title: {$notification->title}\n";
    echo "   Message: {$notification->message}\n";
    echo "   Action URL: {$notification->action_url}\n";
} else {
    echo "❌ No in-app notification found in database.\n";
}

echo "\n📧 Check the Laravel log file for email notification details.\n";
echo "📁 Log location: storage/logs/laravel.log\n";
