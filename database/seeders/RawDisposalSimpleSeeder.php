<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Equipment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RawDisposalSimpleSeeder extends Seeder
{
    public function run(): void
    {
        // Get RAW Disposal company
        $rawDisposal = Company::where('slug', 'raw-disposal')->orWhere('name', 'LIKE', '%RAW Disposal%')->first();

        if (! $rawDisposal) {
            $this->command->error('RAW Disposal company not found.');

            return;
        }

        $this->command->info('Seeding simple data for RAW Disposal...');

        // Create Customers
        $this->command->info('Creating customers...');
        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customer = Customer::firstOrCreate(
                [
                    'organization' => "Test Construction Company $i",
                    'company_id' => $rawDisposal->id,
                ],
                [
                    'first_name' => 'John',
                    'last_name' => "Doe$i",
                    'emails' => json_encode(["customer$i@example.com"]),
                    'phone' => '512-555-'.str_pad(400 + $i, 4, '0', STR_PAD_LEFT),
                    'address' => "$i Main St",
                    'city' => 'Austin',
                    'state' => 'TX',
                    'zip' => '78701',
                    'business_type' => 'construction',
                    'customer_since' => Carbon::now()->subMonths(rand(1, 24)),
                    'portal_access' => true,
                    'portal_password' => Hash::make('password'),
                ]
            );
            $customers[] = $customer;
        }

        // Create Equipment (Dumpsters and Portable Toilets)
        $this->command->info('Creating equipment...');
        $equipmentTypes = [
            ['type' => 'dumpster_20yd', 'name' => '20 Yard Dumpster', 'daily_rate' => 75],
            ['type' => 'dumpster_30yd', 'name' => '30 Yard Dumpster', 'daily_rate' => 85],
            ['type' => 'dumpster_40yd', 'name' => '40 Yard Dumpster', 'daily_rate' => 95],
            ['type' => 'portable_toilet_standard', 'name' => 'Standard Portable Toilet', 'daily_rate' => 25],
            ['type' => 'handwash_station_single', 'name' => 'Single Handwash Station', 'daily_rate' => 15],
        ];

        $equipment = [];
        foreach ($equipmentTypes as $index => $eqType) {
            $eq = Equipment::firstOrCreate(
                [
                    'equipment_number' => 'RAW-EQ-'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'company_id' => $rawDisposal->id,
                ],
                [
                    'name' => $eqType['name'],
                    'type' => $eqType['type'],
                    'manufacturer' => 'Generic',
                    'model' => 'Standard',
                    'serial_number' => 'SN'.str_pad($index + 1, 6, '0', STR_PAD_LEFT),
                    'purchase_date' => Carbon::now()->subMonths(rand(6, 24)),
                    'purchase_price' => rand(2000, 8000),
                    'status' => 'available',
                    'location' => 'Depot',
                    'daily_rental_rate' => $eqType['daily_rate'],
                ]
            );
            $equipment[] = $eq;
        }

        // Create Work Orders
        $this->command->info('Creating work orders...');
        foreach ($customers as $customer) {
            $workOrder = WorkOrder::create([
                'company_id' => $rawDisposal->id,
                'work_order_number' => 'WO-RAW-'.date('Ymd').'-'.str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'type' => 'delivery',
                'status' => 'completed',
                'priority' => 'medium',
                'scheduled_date' => Carbon::now()->subDays(rand(1, 30)),
                'completed_date' => Carbon::now()->subDays(rand(1, 29)),
                'description' => 'Dumpster rental and portable toilet service',
                'service_address' => $customer->address,
                'service_city' => $customer->city,
                'service_state' => $customer->state,
                'service_zip' => $customer->zip,
                'contact_name' => $customer->full_name,
                'contact_phone' => $customer->phone,
                'estimated_duration' => 60,
                'actual_duration' => 55,
                'equipment_used' => json_encode([
                    ['equipment_id' => $equipment[rand(0, 2)]->id, 'quantity' => 1, 'days' => 7],
                    ['equipment_id' => $equipment[3]->id, 'quantity' => 2, 'days' => 7],
                ]),
                'notes' => 'Equipment delivered and set up successfully',
            ]);

            // Create Invoice from Work Order
            if ($workOrder->status === 'completed' && ! $workOrder->invoice_id) {
                $subtotal = 0;
                $lineItems = [];

                // Add equipment rental charges
                $equipmentUsed = json_decode($workOrder->equipment_used, true) ?? [];
                foreach ($equipmentUsed as $item) {
                    $eq = Equipment::find($item['equipment_id']);
                    if ($eq) {
                        $amount = $eq->daily_rental_rate * $item['quantity'] * $item['days'];
                        $subtotal += $amount;
                        $lineItems[] = [
                            'description' => $eq->name.' Rental',
                            'quantity' => $item['quantity'],
                            'unit_price' => $eq->daily_rental_rate,
                            'amount' => $amount,
                            'notes' => $item['days'].' days rental',
                        ];
                    }
                }

                // Add delivery fee
                $deliveryFee = 150;
                $subtotal += $deliveryFee;
                $lineItems[] = [
                    'description' => 'Delivery & Pickup',
                    'quantity' => 1,
                    'unit_price' => $deliveryFee,
                    'amount' => $deliveryFee,
                ];

                $taxRate = 8.25;
                $taxAmount = $subtotal * ($taxRate / 100);
                $totalAmount = $subtotal + $taxAmount;

                $invoice = Invoice::create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $customer->id,
                    'work_order_id' => $workOrder->id,
                    'invoice_number' => 'INV-RAW-'.date('Ymd').'-'.str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'invoice_date' => $workOrder->completed_date,
                    'due_date' => $workOrder->completed_date->addDays(30),
                    'subtotal' => $subtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'balance_due' => $totalAmount,
                    'amount_paid' => 0,
                    'status' => 'sent',
                    'line_items' => json_encode($lineItems),
                    'payment_terms' => 'net30',
                    'notes' => 'Thank you for your business!',
                ]);

                // Update work order with invoice ID
                $workOrder->update(['invoice_id' => $invoice->id, 'invoiced_at' => now()]);

                // Create individual invoice items
                foreach ($lineItems as $item) {
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'amount' => $item['amount'],
                        'notes' => $item['notes'] ?? null,
                    ]);
                }
            }
        }

        // Create one admin user for RAW Disposal
        $this->command->info('Creating admin user for customer portal testing...');
        $adminCustomer = $customers->first();
        if ($adminCustomer) {
            $adminCustomer->update([
                'portal_access' => true,
                'portal_password' => Hash::make('password123'),
                'emails' => json_encode(['admin@rawdisposal.com', 'customer1@example.com']),
            ]);
            $this->command->info('Customer portal login: admin@rawdisposal.com / password123');
        }

        $this->command->info('RAW Disposal simple data seeding completed!');
        $this->command->info('Created:');
        $this->command->info('- 5 Customers with portal access');
        $this->command->info('- 5 Equipment items (dumpsters and portable toilets)');
        $this->command->info('- 5 Work Orders (completed)');
        $this->command->info('- 5 Invoices with line items');
    }
}
