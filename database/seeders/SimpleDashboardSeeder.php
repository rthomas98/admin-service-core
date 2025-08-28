<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SimpleDashboardSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a company
        $company = Company::first() ?? Company::create([
            'name' => 'Raw Disposal Services',
            'address' => '123 Waste Management Way',
            'city' => 'Houston',
            'state' => 'TX',
            'zip' => '77001',
            'phone' => '(713) 555-0100',
            'email' => 'info@rawdisposal.com',
            'website' => 'https://rawdisposal.com',
            'tax_id' => '12-3456789',
            'is_active' => true,
        ]);

        $this->command->info('Creating customers...');
        
        // Create Customers with realistic waste management data
        $businessTypes = ['Restaurant', 'Office Building', 'Retail Store', 'Manufacturing', 'Healthcare', 'School', 'Hotel', 'Residential'];
        
        for ($i = 1; $i <= 100; $i++) {
            $isCommercial = $i % 3 !== 0;
            
            Customer::create([
                'company_id' => $company->id,
                'customer_number' => 'CUST-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'name' => $isCommercial ? null : fake()->name(),
                'organization' => $isCommercial ? fake()->company() : null,
                'first_name' => !$isCommercial ? fake()->firstName() : null,
                'last_name' => !$isCommercial ? fake()->lastName() : null,
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'zip' => fake()->postcode(),
                'customer_since' => Carbon::now()->subDays(rand(30, 730)),
                'business_type' => fake()->randomElement($businessTypes),
                'emails' => json_encode([fake()->safeEmail()]),
                'notification_preferences' => json_encode([
                    'service_reminder' => ['enabled' => true, 'method' => 'email'],
                    'payment_due' => ['enabled' => true, 'method' => 'email'],
                    'emergency' => ['enabled' => true, 'method' => 'sms'],
                ]),
                'notifications_enabled' => true,
                'preferred_notification_method' => 'email',
                'sms_number' => fake()->optional()->phoneNumber(),
            ]);
        }

        $this->command->info('Creating drivers...');
        
        // Create simple drivers
        $drivers = [];
        for ($i = 1; $i <= 25; $i++) {
            $drivers[] = Driver::create([
                'company_id' => $company->id,
                'employee_id' => 'DRV-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'license_number' => strtoupper(Str::random(8)),
                'license_class' => fake()->randomElement(['CDL-A', 'CDL-B']),
                'license_expiry_date' => Carbon::now()->addMonths(rand(6, 36)),
                'license_state' => fake()->stateAbbr(),
                'vehicle_type' => 'Waste Collection Truck',
                'hourly_rate' => fake()->randomFloat(2, 18, 35),
                'status' => $i <= 20 ? 'active' : 'inactive',
                'hired_date' => Carbon::now()->subDays(rand(30, 1095)),
                'emergency_contact' => fake()->name(),
                'emergency_phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'zip' => fake()->postcode(),
                'safety_score' => rand(85, 100),
            ]);
        }

        $this->command->info('Creating invoices and payments...');
        
        // Create Invoices with realistic payment patterns
        $customers = Customer::where('company_id', $company->id)->get();
        $invoiceCount = 0;
        $paymentCount = 0;
        
        foreach ($customers as $customer) {
            // Each customer gets 6 months of invoices
            for ($month = 5; $month >= 0; $month--) {
                $invoiceDate = Carbon::now()->subMonths($month)->startOfMonth()->addDays(rand(0, 5));
                $dueDate = $invoiceDate->copy()->addDays(30);
                
                // Base amount varies by business type
                $baseAmount = match($customer->business_type) {
                    'Restaurant' => rand(500, 2000),
                    'Office Building' => rand(300, 1500),
                    'Manufacturing' => rand(1000, 5000),
                    'Healthcare' => rand(800, 3000),
                    'School' => rand(400, 1200),
                    'Hotel' => rand(600, 2500),
                    'Residential' => rand(35, 150),
                    default => rand(200, 1000),
                };
                
                // Add some variance
                $amount = $baseAmount + (fake()->randomFloat(2, -50, 100));
                
                // Older invoices are more likely to be paid
                if ($month > 1) {
                    $status = fake()->randomElement(['paid', 'paid', 'paid', 'paid', 'overdue']); // 80% paid
                } elseif ($month == 1) {
                    $status = fake()->randomElement(['paid', 'paid', 'pending', 'overdue']);
                } else {
                    $status = fake()->randomElement(['pending', 'pending', 'sent', 'overdue']);
                }

                $taxAmount = $amount * 0.0825;
                $totalAmount = $amount + $taxAmount;
                
                // Get or create a service order for this customer
                $serviceOrder = \App\Models\ServiceOrder::where('customer_id', $customer->id)
                    ->where('company_id', $company->id)
                    ->first();
                    
                if (!$serviceOrder) {
                    $serviceOrder = \App\Models\ServiceOrder::create([
                        'company_id' => $company->id,
                        'customer_id' => $customer->id,
                        'order_number' => 'SO-' . str_pad(\App\Models\ServiceOrder::count() + 1, 6, '0', STR_PAD_LEFT),
                        'service_type' => 'Regular Pickup',
                        'scheduled_date' => $invoiceDate,
                        'status' => 'completed',
                    ]);
                }
                
                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'service_order_id' => $serviceOrder->id,
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-' . date('Y') . '-' . str_pad(++$invoiceCount, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'subtotal' => $amount,
                    'tax_rate' => 8.25,
                    'tax_amount' => $taxAmount,
                    'total_amount' => $totalAmount,
                    'balance_due' => $status === 'paid' ? 0 : $totalAmount,
                    'amount_paid' => $status === 'paid' ? $totalAmount : 0,
                    'status' => $status,
                    'sent_date' => in_array($status, ['sent', 'paid', 'overdue']) ? $invoiceDate->copy()->addDay() : null,
                    'paid_date' => $status === 'paid' ? $dueDate->copy()->subDays(rand(0, 25)) : null,
                    'line_items' => [
                        [
                            'description' => 'Waste Collection Service - ' . $invoiceDate->format('F Y'),
                            'quantity' => 1,
                            'rate' => $amount,
                            'amount' => $amount
                        ]
                    ],
                    'notes' => 'Thank you for your business!',
                    'terms_conditions' => 'Net 30 - Payment due within 30 days',
                ]);

                // Create payment if paid
                if ($status === 'paid') {
                    $paymentMethod = fake()->randomElement(['credit_card', 'check', 'ach', 'cash']);
                    $feeAmount = $paymentMethod === 'credit_card' ? ($invoice->total_amount * 0.029) : 0; // 2.9% fee for credit cards
                    
                    Payment::create([
                        'company_id' => $company->id,
                        'invoice_id' => $invoice->id,
                        'customer_id' => $customer->id,
                        'payment_date' => $invoice->paid_date,
                        'amount' => $invoice->total_amount,
                        'payment_method' => $paymentMethod,
                        'reference_number' => 'PAY-' . strtoupper(Str::random(8)),
                        'status' => 'completed',
                        'fee_amount' => $feeAmount,
                        'net_amount' => $invoice->total_amount - $feeAmount,
                        'check_number' => $paymentMethod === 'check' ? rand(1000, 9999) : null,
                    ]);
                    $paymentCount++;
                }
            }
        }

        // Output summary
        $this->command->info('');
        $this->command->info('Dashboard seed data created successfully!');
        $this->command->info('========================================');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Customers', Customer::where('company_id', $company->id)->count()],
                ['Drivers', Driver::where('company_id', $company->id)->count()],
                ['Invoices', Invoice::where('company_id', $company->id)->count()],
                ['Payments', Payment::where('company_id', $company->id)->count()],
                ['Overdue Invoices', Invoice::where('company_id', $company->id)->where('status', 'overdue')->count()],
                ['Pending Invoices', Invoice::where('company_id', $company->id)->whereIn('status', ['pending', 'sent'])->count()],
            ]
        );
        
        // Revenue summary
        $totalRevenue = Payment::where('company_id', $company->id)
            ->whereMonth('payment_date', Carbon::now()->month)
            ->sum('amount');
        
        $lastMonthRevenue = Payment::where('company_id', $company->id)
            ->whereMonth('payment_date', Carbon::now()->subMonth()->month)
            ->sum('amount');
            
        $this->command->info('');
        $this->command->info('Revenue Summary:');
        $this->command->info('Current Month: $' . number_format($totalRevenue, 2));
        $this->command->info('Last Month: $' . number_format($lastMonthRevenue, 2));
        
        $overdueAmount = Invoice::where('company_id', $company->id)
            ->where('status', 'overdue')
            ->sum('total_amount');
            
        $this->command->info('Overdue Amount: $' . number_format($overdueAmount, 2));
        $this->command->info('');
    }
}