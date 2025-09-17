<?php

namespace App\Jobs;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\WorkOrder;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateInvoiceFromWorkOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected WorkOrder $workOrder,
        protected bool $autoSend = false
    ) {}

    public function handle(): void
    {
        try {
            DB::beginTransaction();

            // Check if invoice already exists for this work order
            $existingInvoice = Invoice::where('service_order_id', $this->workOrder->service_order_id)
                ->where('customer_id', $this->workOrder->customer_id)
                ->whereDate('created_at', now())
                ->first();

            if ($existingInvoice) {
                Log::info('Invoice already exists for work order', [
                    'work_order_id' => $this->workOrder->id,
                    'invoice_id' => $existingInvoice->id,
                ]);

                return;
            }

            // Create the invoice
            $invoice = new Invoice([
                'company_id' => $this->workOrder->company_id,
                'customer_id' => $this->workOrder->customer_id,
                'service_order_id' => $this->workOrder->service_order_id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => now()->addDays(30), // NET 30 terms
                'status' => InvoiceStatus::Draft,
                'tax_rate' => 8.75, // Texas state tax rate
            ]);

            // Set billing information from customer
            if ($this->workOrder->customer) {
                $invoice->billing_address = $this->workOrder->customer->address;
                $invoice->billing_city = $this->workOrder->customer->city;
                $invoice->billing_parish = $this->workOrder->customer->state ?? 'TX';
                $invoice->billing_postal_code = $this->workOrder->customer->zip;
            }

            $invoice->save();

            // Create invoice items based on work order type
            $items = [];
            $subtotal = 0;

            // Equipment rental charges
            if ($this->workOrder->equipment_id && $this->workOrder->equipment) {
                $equipment = $this->workOrder->equipment;
                $rentalDays = 1;

                if ($this->workOrder->start_date && $this->workOrder->end_date) {
                    $rentalDays = $this->workOrder->start_date->diffInDays($this->workOrder->end_date) ?: 1;
                }

                // Rental charge
                $dailyRate = $equipment->daily_rate ?? $equipment->type->defaultDailyRate();
                $rentalTotal = $dailyRate * $rentalDays;

                $items[] = new InvoiceItem([
                    'invoice_id' => $invoice->id,
                    'work_order_id' => $this->workOrder->id,
                    'equipment_id' => $equipment->id,
                    'type' => 'equipment_rental',
                    'description' => "Rental: {$equipment->name} - {$equipment->type->label()}",
                    'quantity' => $rentalDays,
                    'unit_price' => $dailyRate,
                    'tax_rate' => $invoice->tax_rate,
                    'tax_amount' => $rentalTotal * ($invoice->tax_rate / 100),
                    'total' => $rentalTotal + ($rentalTotal * ($invoice->tax_rate / 100)),
                    'rental_start_date' => $this->workOrder->start_date,
                    'rental_end_date' => $this->workOrder->end_date,
                    'rental_days' => $rentalDays,
                    'metadata' => [
                        'equipment_type' => $equipment->type->value,
                        'equipment_number' => $equipment->equipment_number,
                    ],
                ]);

                $subtotal += $rentalTotal;

                // Delivery fee
                if ($equipment->delivery_fee > 0) {
                    $deliveryTotal = $equipment->delivery_fee;
                    $items[] = new InvoiceItem([
                        'invoice_id' => $invoice->id,
                        'work_order_id' => $this->workOrder->id,
                        'equipment_id' => $equipment->id,
                        'type' => 'delivery',
                        'description' => 'Delivery Fee',
                        'quantity' => 1,
                        'unit_price' => $deliveryTotal,
                        'tax_rate' => 0, // Usually delivery fees are not taxed
                        'tax_amount' => 0,
                        'total' => $deliveryTotal,
                    ]);

                    $subtotal += $deliveryTotal;
                }

                // Pickup fee
                if ($equipment->pickup_fee > 0) {
                    $pickupTotal = $equipment->pickup_fee;
                    $items[] = new InvoiceItem([
                        'invoice_id' => $invoice->id,
                        'work_order_id' => $this->workOrder->id,
                        'equipment_id' => $equipment->id,
                        'type' => 'pickup',
                        'description' => 'Pickup Fee',
                        'quantity' => 1,
                        'unit_price' => $pickupTotal,
                        'tax_rate' => 0, // Usually pickup fees are not taxed
                        'tax_amount' => 0,
                        'total' => $pickupTotal,
                    ]);

                    $subtotal += $pickupTotal;
                }
            }

            // Service charges
            if ($this->workOrder->service_description) {
                $servicePrice = $this->workOrder->estimated_cost ?? 0;
                $items[] = new InvoiceItem([
                    'invoice_id' => $invoice->id,
                    'work_order_id' => $this->workOrder->id,
                    'type' => 'service',
                    'description' => $this->workOrder->service_description,
                    'quantity' => 1,
                    'unit_price' => $servicePrice,
                    'tax_rate' => $invoice->tax_rate,
                    'tax_amount' => $servicePrice * ($invoice->tax_rate / 100),
                    'total' => $servicePrice + ($servicePrice * ($invoice->tax_rate / 100)),
                    'service_date' => $this->workOrder->scheduled_date,
                ]);

                $subtotal += $servicePrice;
            }

            // Save all invoice items
            foreach ($items as $item) {
                $item->save();
            }

            // Update invoice totals
            $invoice->load('items');
            $invoice->updateTotals();

            // Mark work order as invoiced
            $this->workOrder->update([
                'invoice_id' => $invoice->id,
                'invoiced_at' => now(),
            ]);

            // Auto-send if requested
            if ($this->autoSend) {
                $invoice->markAsSent();

                // Send notification to customer
                if ($invoice->customer) {
                    app(NotificationService::class)->sendFromTemplate(
                        'invoice-created',
                        $invoice->customer,
                        [
                            'customer_name' => $invoice->customer->full_name,
                            'invoice_number' => $invoice->invoice_number,
                            'total_amount' => number_format($invoice->total_amount, 2),
                            'due_date' => $invoice->due_date->format('M d, Y'),
                            'view_url' => url("/customer/invoices/{$invoice->id}"),
                        ]
                    );
                }
            }

            DB::commit();

            Log::info('Invoice generated successfully from work order', [
                'work_order_id' => $this->workOrder->id,
                'invoice_id' => $invoice->id,
                'total_amount' => $invoice->total_amount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate invoice from work order', [
                'work_order_id' => $this->workOrder->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
