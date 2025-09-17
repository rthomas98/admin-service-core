# RAW Disposal Technical Specifications

## Tomato Invoices Integration Plan

### Installation & Configuration

```bash
# Install Tomato Invoices plugin
composer require tomatophp/filament-invoices

# Run installation
php artisan filament-invoices:install

# Publish configuration
php artisan vendor:publish --tag="filament-invoices-config"
php artisan vendor:publish --tag="filament-invoices-views"
php artisan vendor:publish --tag="filament-invoices-lang"
```

### Plugin Registration

```php
// app/Providers/Filament/AdminPanelProvider.php
use TomatoPHP\FilamentInvoices\FilamentInvoicesPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->tenant(Company::class)
        ->tenantRegistration(RegisterCompany::class)
        ->tenantProfile(EditCompanyProfile::class)
        ->plugins([
            FilamentInvoicesPlugin::make()
                ->useCustomInvoiceModel(RawDisposalInvoice::class)
                ->useCustomInvoiceItemModel(RawDisposalInvoiceItem::class)
                ->allowInvoiceTypes([
                    'rental' => 'Equipment Rental',
                    'service' => 'Service',
                    'disposal' => 'Waste Disposal',
                    'emergency' => 'Emergency Service',
                ])
                ->defaultDueDays(30)
                ->enableCustomerPortal()
                ->enablePaymentGateways(['stripe', 'square'])
        ]);
}
```

### Custom Invoice Models

```php
// app/Models/RawDisposalInvoice.php
namespace App\Models;

use TomatoPHP\FilamentInvoices\Models\Invoice;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawDisposalInvoice extends Invoice
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'work_order_id',
        'equipment_id',
        'invoice_type',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'paid_amount',
        'balance_due',
        'notes',
        'terms',
        'payment_method',
        'payment_date',
        'company_id',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = match($this->invoice_type) {
            'rental' => 'RNT',
            'service' => 'SVC',
            'disposal' => 'DSP',
            'emergency' => 'EMG',
            default => 'INV'
        };

        $year = now()->format('Y');
        $month = now()->format('m');

        $lastInvoice = self::where('company_id', $this->company_id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->latest('id')
            ->first();

        $sequence = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, -4)) + 1) : 1;

        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}
```

### Invoice Service Implementation

```php
// app/Services/RawDisposalInvoiceService.php
namespace App\Services;

use App\Models\WorkOrder;
use App\Models\Equipment;
use App\Models\Customer;
use App\Models\RawDisposalInvoice;
use TomatoPHP\FilamentInvoices\Facades\FilamentInvoices;
use TomatoPHP\FilamentInvoices\Services\Contracts\InvoiceItem;

class RawDisposalInvoiceService
{
    public function createWorkOrderInvoice(WorkOrder $workOrder): RawDisposalInvoice
    {
        $items = [];

        // Add equipment rental charges
        if ($workOrder->equipment) {
            $rentalDays = $workOrder->rental_start_date->diffInDays($workOrder->rental_end_date);

            $items[] = InvoiceItem::make('Equipment Rental - ' . $workOrder->equipment->name)
                ->description($workOrder->equipment->description)
                ->qty($rentalDays)
                ->price($workOrder->equipment->daily_rate)
                ->tax($this->calculateTax($workOrder->equipment->daily_rate * $rentalDays));
        }

        // Add delivery charges
        if ($workOrder->delivery_fee > 0) {
            $items[] = InvoiceItem::make('Delivery Service')
                ->description('Equipment delivery to ' . $workOrder->delivery_address)
                ->qty(1)
                ->price($workOrder->delivery_fee)
                ->tax($this->calculateTax($workOrder->delivery_fee));
        }

        // Add pickup charges
        if ($workOrder->pickup_fee > 0) {
            $items[] = InvoiceItem::make('Pickup Service')
                ->description('Equipment pickup from ' . $workOrder->pickup_address)
                ->qty(1)
                ->price($workOrder->pickup_fee)
                ->tax($this->calculateTax($workOrder->pickup_fee));
        }

        // Add disposal fees if applicable
        if ($workOrder->disposal_fee > 0) {
            $items[] = InvoiceItem::make('Waste Disposal Fee')
                ->description('Disposal at ' . $workOrder->disposalSite->name)
                ->qty($workOrder->disposal_weight)
                ->price($workOrder->disposal_rate_per_ton)
                ->tax($this->calculateTax($workOrder->disposal_fee));
        }

        // Add emergency service surcharge if applicable
        if ($workOrder->is_emergency) {
            $emergencyFee = $this->calculateEmergencyFee($workOrder);
            $items[] = InvoiceItem::make('Emergency Service Surcharge')
                ->description('24/7 Emergency Response')
                ->qty(1)
                ->price($emergencyFee)
                ->tax($this->calculateTax($emergencyFee));
        }

        // Create invoice using Tomato Invoices
        $invoice = FilamentInvoices::create()
            ->for($workOrder->customer)
            ->from($workOrder->company)
            ->type('work_order')
            ->dueDate(now()->addDays($workOrder->customer->payment_terms ?? 30))
            ->items($items)
            ->notes($this->generateInvoiceNotes($workOrder))
            ->terms($this->getPaymentTerms($workOrder->customer))
            ->save();

        // Link invoice to work order
        $workOrder->update(['invoice_id' => $invoice->id]);

        // Send invoice notification
        $this->sendInvoiceNotification($invoice);

        return $invoice;
    }

    public function createRecurringInvoice(Customer $customer, array $services): RawDisposalInvoice
    {
        $items = [];

        foreach ($services as $service) {
            $items[] = InvoiceItem::make($service['name'])
                ->description($service['description'])
                ->qty($service['quantity'])
                ->price($service['rate'])
                ->tax($this->calculateTax($service['quantity'] * $service['rate']));
        }

        return FilamentInvoices::create()
            ->for($customer)
            ->from($customer->company)
            ->type('recurring')
            ->dueDate(now()->addDays($customer->payment_terms ?? 30))
            ->items($items)
            ->recurring(true)
            ->recurringPeriod('monthly')
            ->save();
    }

    private function calculateTax(float $amount): float
    {
        $taxRate = config('invoices.tax_rate', 0.08); // 8% default
        return round($amount * $taxRate, 2);
    }

    private function calculateEmergencyFee(WorkOrder $workOrder): float
    {
        $baseFee = 150.00;

        // Add weekend surcharge
        if ($workOrder->created_at->isWeekend()) {
            $baseFee += 50.00;
        }

        // Add after-hours surcharge
        $hour = $workOrder->created_at->hour;
        if ($hour < 7 || $hour > 18) {
            $baseFee += 75.00;
        }

        return $baseFee;
    }

    private function generateInvoiceNotes(WorkOrder $workOrder): string
    {
        $notes = "Work Order #: {$workOrder->work_order_number}\n";
        $notes .= "Service Date: {$workOrder->service_date->format('M d, Y')}\n";

        if ($workOrder->driver) {
            $notes .= "Service Technician: {$workOrder->driver->name}\n";
        }

        if ($workOrder->special_instructions) {
            $notes .= "Special Instructions: {$workOrder->special_instructions}\n";
        }

        return $notes;
    }

    private function getPaymentTerms(Customer $customer): string
    {
        $terms = "Payment is due within {$customer->payment_terms} days of invoice date.\n";
        $terms .= "Late payments subject to 1.5% monthly interest charge.\n";

        if ($customer->credit_limit) {
            $terms .= "Credit Limit: $" . number_format($customer->credit_limit, 2) . "\n";
        }

        return $terms;
    }

    private function sendInvoiceNotification(RawDisposalInvoice $invoice): void
    {
        // Send email notification
        $invoice->customer->notify(new InvoiceCreatedNotification($invoice));

        // Send SMS if enabled
        if ($invoice->customer->sms_notifications) {
            app(SmsService::class)->sendInvoiceAlert($invoice);
        }
    }
}
```

### Customer Portal Invoice Integration

```tsx
// resources/js/Pages/Customer/Invoices.tsx
import React, { useState, useEffect } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import CustomerLayout from '@/Layouts/CustomerLayout';
import { Invoice, PaymentMethod } from '@/types';
import { formatCurrency, formatDate } from '@/utils';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { CreditCard, Download, Eye, DollarSign } from 'lucide-react';

interface Props {
    invoices: Invoice[];
    outstandingBalance: number;
    paymentMethods: PaymentMethod[];
}

export default function CustomerInvoices({ invoices, outstandingBalance, paymentMethods }: Props) {
    const [selectedInvoices, setSelectedInvoices] = useState<number[]>([]);
    const [processingPayment, setProcessingPayment] = useState(false);

    const handlePayInvoice = async (invoiceId: number) => {
        setProcessingPayment(true);

        router.post(`/customer/invoices/${invoiceId}/pay`, {
            payment_method_id: paymentMethods[0]?.id,
            amount: invoices.find(i => i.id === invoiceId)?.balance_due
        }, {
            onSuccess: () => {
                setProcessingPayment(false);
                // Show success notification
            },
            onError: () => {
                setProcessingPayment(false);
                // Show error notification
            }
        });
    };

    const handleBulkPayment = async () => {
        if (selectedInvoices.length === 0) return;

        const totalAmount = selectedInvoices.reduce((sum, id) => {
            const invoice = invoices.find(i => i.id === id);
            return sum + (invoice?.balance_due || 0);
        }, 0);

        router.post('/customer/invoices/bulk-pay', {
            invoice_ids: selectedInvoices,
            payment_method_id: paymentMethods[0]?.id,
            amount: totalAmount
        });
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'paid': return 'success';
            case 'partial': return 'warning';
            case 'overdue': return 'destructive';
            case 'pending': return 'secondary';
            default: return 'default';
        }
    };

    return (
        <CustomerLayout>
            <Head title="Invoices" />

            <div className="space-y-6">
                {/* Outstanding Balance Card */}
                <Card>
                    <CardHeader>
                        <CardTitle>Account Summary</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p className="text-sm text-muted-foreground">Outstanding Balance</p>
                                <p className="text-2xl font-bold text-destructive">
                                    {formatCurrency(outstandingBalance)}
                                </p>
                            </div>
                            <div>
                                <p className="text-sm text-muted-foreground">Open Invoices</p>
                                <p className="text-2xl font-bold">
                                    {invoices.filter(i => i.status !== 'paid').length}
                                </p>
                            </div>
                            <div className="flex items-end">
                                <Button
                                    onClick={handleBulkPayment}
                                    disabled={selectedInvoices.length === 0 || processingPayment}
                                    className="w-full"
                                >
                                    <DollarSign className="mr-2 h-4 w-4" />
                                    Pay Selected
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Invoices List */}
                <Card>
                    <CardHeader>
                        <CardTitle>Invoice History</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {invoices.map((invoice) => (
                                <div
                                    key={invoice.id}
                                    className="flex items-center justify-between p-4 border rounded-lg hover:bg-accent/50 transition-colors"
                                >
                                    <div className="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            checked={selectedInvoices.includes(invoice.id)}
                                            onChange={(e) => {
                                                if (e.target.checked) {
                                                    setSelectedInvoices([...selectedInvoices, invoice.id]);
                                                } else {
                                                    setSelectedInvoices(selectedInvoices.filter(id => id !== invoice.id));
                                                }
                                            }}
                                            disabled={invoice.status === 'paid'}
                                            className="h-4 w-4"
                                        />
                                        <div>
                                            <div className="flex items-center space-x-2">
                                                <p className="font-medium">#{invoice.invoice_number}</p>
                                                <Badge variant={getStatusColor(invoice.status)}>
                                                    {invoice.status}
                                                </Badge>
                                            </div>
                                            <p className="text-sm text-muted-foreground">
                                                Due: {formatDate(invoice.due_date)}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-center space-x-4">
                                        <div className="text-right">
                                            <p className="font-medium">{formatCurrency(invoice.total)}</p>
                                            {invoice.balance_due > 0 && (
                                                <p className="text-sm text-destructive">
                                                    Balance: {formatCurrency(invoice.balance_due)}
                                                </p>
                                            )}
                                        </div>

                                        <div className="flex space-x-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => window.open(`/customer/invoices/${invoice.id}/view`, '_blank')}
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => window.open(`/customer/invoices/${invoice.id}/download`, '_blank')}
                                            >
                                                <Download className="h-4 w-4" />
                                            </Button>
                                            {invoice.status !== 'paid' && (
                                                <Button
                                                    size="sm"
                                                    onClick={() => handlePayInvoice(invoice.id)}
                                                    disabled={processingPayment}
                                                >
                                                    <CreditCard className="h-4 w-4 mr-2" />
                                                    Pay Now
                                                </Button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </div>
        </CustomerLayout>
    );
}
```

### Work Order to Invoice Automation

```php
// app/Observers/WorkOrderObserver.php
namespace App\Observers;

use App\Models\WorkOrder;
use App\Services\RawDisposalInvoiceService;
use App\Jobs\GenerateInvoiceJob;

class WorkOrderObserver
{
    protected RawDisposalInvoiceService $invoiceService;

    public function __construct(RawDisposalInvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function updated(WorkOrder $workOrder)
    {
        // Auto-generate invoice when work order is completed
        if ($workOrder->isDirty('status') &&
            $workOrder->status === 'completed' &&
            !$workOrder->invoice_id) {

            GenerateInvoiceJob::dispatch($workOrder)
                ->delay(now()->addMinutes(5));
        }
    }
}
```

### Payment Gateway Integration

```php
// app/Services/PaymentGatewayService.php
namespace App\Services;

use App\Models\RawDisposalInvoice;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Charge;
use Square\SquareClient;

class PaymentGatewayService
{
    protected $stripeClient;
    protected $squareClient;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $this->squareClient = new SquareClient([
            'accessToken' => config('services.square.access_token'),
            'environment' => config('services.square.environment'),
        ]);
    }

    public function processPayment(RawDisposalInvoice $invoice, array $paymentData): Payment
    {
        $gateway = $paymentData['gateway'] ?? 'stripe';

        try {
            $result = match($gateway) {
                'stripe' => $this->processStripePayment($invoice, $paymentData),
                'square' => $this->processSquarePayment($invoice, $paymentData),
                'ach' => $this->processACHPayment($invoice, $paymentData),
                default => throw new \Exception('Invalid payment gateway')
            };

            // Record payment
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $paymentData['amount'],
                'payment_method' => $gateway,
                'transaction_id' => $result['transaction_id'],
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => $result['metadata'] ?? [],
            ]);

            // Update invoice
            $invoice->update([
                'paid_amount' => $invoice->paid_amount + $payment->amount,
                'balance_due' => max(0, $invoice->balance_due - $payment->amount),
                'status' => $invoice->balance_due <= $payment->amount ? 'paid' : 'partial',
                'payment_date' => now(),
            ]);

            // Send receipt
            $this->sendPaymentReceipt($payment);

            return $payment;

        } catch (\Exception $e) {
            // Log error and create failed payment record
            \Log::error('Payment processing failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return Payment::create([
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'amount' => $paymentData['amount'],
                'payment_method' => $gateway,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function processStripePayment(RawDisposalInvoice $invoice, array $data): array
    {
        $charge = Charge::create([
            'amount' => $data['amount'] * 100, // Convert to cents
            'currency' => 'usd',
            'source' => $data['token'],
            'description' => "Invoice #{$invoice->invoice_number}",
            'metadata' => [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
            ],
        ]);

        return [
            'transaction_id' => $charge->id,
            'metadata' => [
                'stripe_charge_id' => $charge->id,
                'receipt_url' => $charge->receipt_url,
            ],
        ];
    }

    private function processSquarePayment(RawDisposalInvoice $invoice, array $data): array
    {
        $paymentsApi = $this->squareClient->getPaymentsApi();

        $result = $paymentsApi->createPayment([
            'source_id' => $data['nonce'],
            'idempotency_key' => uniqid(),
            'amount_money' => [
                'amount' => $data['amount'] * 100,
                'currency' => 'USD'
            ],
            'reference_id' => $invoice->invoice_number,
            'note' => "Payment for Invoice #{$invoice->invoice_number}",
        ]);

        if ($result->isSuccess()) {
            $payment = $result->getResult()->getPayment();

            return [
                'transaction_id' => $payment->getId(),
                'metadata' => [
                    'square_payment_id' => $payment->getId(),
                    'receipt_url' => $payment->getReceiptUrl(),
                ],
            ];
        }

        throw new \Exception($result->getErrors()[0]->getDetail());
    }

    private function processACHPayment(RawDisposalInvoice $invoice, array $data): array
    {
        // Implement ACH payment processing
        // This would typically integrate with a service like Plaid or Dwolla

        return [
            'transaction_id' => 'ACH-' . uniqid(),
            'metadata' => [
                'routing_number' => substr($data['routing_number'], -4),
                'account_number' => substr($data['account_number'], -4),
                'processing_days' => 3,
            ],
        ];
    }

    private function sendPaymentReceipt(Payment $payment): void
    {
        $payment->customer->notify(new PaymentReceivedNotification($payment));
    }
}
```

### Database Migrations for Invoice Extensions

```php
// database/migrations/2024_XX_XX_extend_invoices_for_raw_disposal.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable()->constrained();
            $table->foreignId('equipment_id')->nullable()->constrained();
            $table->string('invoice_type', 50)->default('standard');
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('pickup_fee', 10, 2)->nullable();
            $table->decimal('disposal_fee', 10, 2)->nullable();
            $table->decimal('emergency_fee', 10, 2)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_period', 20)->nullable();
            $table->date('next_invoice_date')->nullable();
            $table->integer('payment_terms')->default(30);
            $table->text('internal_notes')->nullable();

            $table->index(['company_id', 'invoice_type']);
            $table->index(['customer_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
            $table->dropForeign(['equipment_id']);
            $table->dropColumn([
                'work_order_id',
                'equipment_id',
                'invoice_type',
                'delivery_fee',
                'pickup_fee',
                'disposal_fee',
                'emergency_fee',
                'is_recurring',
                'recurring_period',
                'next_invoice_date',
                'payment_terms',
                'internal_notes'
            ]);
        });
    }
};
```

## Next Steps

1. **Immediate Actions**:
   - Install Tomato Invoices plugin
   - Update EquipmentType enum with RAW Disposal types
   - Create missing Filament resources for waste management models

2. **Week 1-2 Sprint**:
   - Implement invoice customization for RAW Disposal
   - Build customer portal invoice interface
   - Set up payment gateway integrations

3. **Testing Requirements**:
   - Unit tests for invoice generation
   - Integration tests for payment processing
   - E2E tests for customer portal workflows

4. **Documentation Needs**:
   - API documentation for invoice endpoints
   - User guides for invoice management
   - Payment gateway configuration guide