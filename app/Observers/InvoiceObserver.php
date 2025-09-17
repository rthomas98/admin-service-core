<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        // Send notification when invoice is created and not in draft status
        if ($invoice->status !== InvoiceStatus::Draft && $invoice->customer) {
            $this->sendInvoiceCreatedNotification($invoice);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Check if status changed
        if ($invoice->isDirty('status')) {
            $oldStatusValue = $invoice->getOriginal('status');
            $oldStatus = is_string($oldStatusValue) ? InvoiceStatus::from($oldStatusValue) : $oldStatusValue;
            $newStatus = $invoice->status;

            // Handle status transitions
            if ($oldStatus === InvoiceStatus::Draft && $newStatus === InvoiceStatus::Sent) {
                $this->sendInvoiceCreatedNotification($invoice);
            } elseif ($newStatus === InvoiceStatus::Paid) {
                $this->sendPaymentReceivedNotification($invoice);
            } elseif ($newStatus === InvoiceStatus::Overdue) {
                $this->sendOverdueNotification($invoice);
            }
        }

        // Check if invoice is approaching due date (3 days before)
        if (! $invoice->isDirty('status') &&
            $invoice->status === InvoiceStatus::Sent &&
            $invoice->due_date &&
            $invoice->due_date->isToday()->addDays(3)) {
            $this->sendPaymentReminderNotification($invoice);
        }
    }

    /**
     * Send invoice created notification
     */
    private function sendInvoiceCreatedNotification(Invoice $invoice): void
    {
        if (! $invoice->customer) {
            return;
        }

        try {
            // Send email notification
            $this->notificationService->sendFromTemplate(
                'invoice-created',
                $invoice->customer,
                [
                    'customer_name' => $invoice->customer->full_name,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => number_format($invoice->total_amount, 2),
                    'due_date' => $invoice->due_date?->format('M d, Y') ?? 'N/A',
                    'view_url' => url("/customer/invoices/{$invoice->id}"),
                ]
            );

            // Create in-app notification for customer dashboard
            Notification::create([
                'company_id' => $invoice->company_id,
                'recipient_type' => 'App\Models\Customer',
                'recipient_id' => $invoice->customer_id,
                'type' => 'in_app',
                'channel' => 'database',  // Added required channel field
                'category' => 'invoice',
                'subject' => 'New Invoice',  // Added subject field
                'message' => "Invoice #{$invoice->invoice_number} for \$".number_format($invoice->total_amount, 2).' has been issued.',
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'due_date' => $invoice->due_date?->toISOString(),
                    'action_url' => "/customer/invoices/{$invoice->id}",
                ],
                'status' => 'pending',
                'recipient_email' => $invoice->customer->getNotificationEmail(),
            ]);

            Log::info('Invoice created notification sent', [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send invoice created notification', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send payment received notification
     */
    private function sendPaymentReceivedNotification(Invoice $invoice): void
    {
        if (! $invoice->customer) {
            return;
        }

        try {
            $this->notificationService->sendFromTemplate(
                'payment-received',
                $invoice->customer,
                [
                    'customer_name' => $invoice->customer->full_name,
                    'invoice_number' => $invoice->invoice_number,
                    'payment_amount' => number_format($invoice->amount_paid, 2),
                    'payment_date' => now()->format('M d, Y'),
                ]
            );

            // Create in-app notification
            Notification::create([
                'company_id' => $invoice->company_id,
                'recipient_type' => 'App\Models\Customer',
                'recipient_id' => $invoice->customer_id,
                'type' => 'in_app',
                'channel' => 'database',
                'category' => 'invoice',
                'subject' => 'Payment Received',
                'message' => "Payment for Invoice #{$invoice->invoice_number} has been received. Thank you!",
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'payment_amount' => $invoice->amount_paid,
                    'action_url' => "/customer/invoices/{$invoice->id}",
                ],
                'status' => 'pending',
                'recipient_email' => $invoice->customer->getNotificationEmail(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment received notification', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send overdue notification
     */
    private function sendOverdueNotification(Invoice $invoice): void
    {
        if (! $invoice->customer) {
            return;
        }

        try {
            $this->notificationService->sendFromTemplate(
                'payment-overdue',
                $invoice->customer,
                [
                    'customer_name' => $invoice->customer->full_name,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => number_format($invoice->balance_due, 2),
                    'due_date' => $invoice->due_date?->format('M d, Y') ?? 'N/A',
                    'days_overdue' => $invoice->due_date ? now()->diffInDays($invoice->due_date) : 0,
                ]
            );

            // Create in-app notification
            Notification::create([
                'company_id' => $invoice->company_id,
                'recipient_type' => 'App\Models\Customer',
                'recipient_id' => $invoice->customer_id,
                'type' => 'in_app',
                'channel' => 'database',
                'category' => 'invoice',
                'subject' => 'Invoice Overdue',
                'message' => "Invoice #{$invoice->invoice_number} is overdue. Please make payment as soon as possible.",
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'balance_due' => $invoice->balance_due,
                    'days_overdue' => $invoice->due_date ? now()->diffInDays($invoice->due_date) : 0,
                    'action_url' => "/customer/invoices/{$invoice->id}",
                ],
                'status' => 'pending',
                'recipient_email' => $invoice->customer->getNotificationEmail(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send overdue notification', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send payment reminder notification
     */
    private function sendPaymentReminderNotification(Invoice $invoice): void
    {
        if (! $invoice->customer) {
            return;
        }

        try {
            $this->notificationService->sendFromTemplate(
                'payment-due',
                $invoice->customer,
                [
                    'customer_name' => $invoice->customer->full_name,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => number_format($invoice->balance_due, 2),
                    'due_date' => $invoice->due_date?->format('M d, Y') ?? 'N/A',
                ]
            );

            // Create in-app notification
            Notification::create([
                'company_id' => $invoice->company_id,
                'recipient_type' => 'App\Models\Customer',
                'recipient_id' => $invoice->customer_id,
                'type' => 'in_app',
                'channel' => 'database',
                'category' => 'invoice',
                'subject' => 'Payment Due Soon',
                'message' => "Invoice #{$invoice->invoice_number} is due on {$invoice->due_date->format('M d, Y')}.",
                'data' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'balance_due' => $invoice->balance_due,
                    'due_date' => $invoice->due_date->toISOString(),
                    'action_url' => "/customer/invoices/{$invoice->id}",
                ],
                'status' => 'pending',
                'recipient_email' => $invoice->customer->getNotificationEmail(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder notification', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        // Clean up any pending notifications
        Notification::where('data->invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->delete();
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     */
    public function forceDeleted(Invoice $invoice): void
    {
        // Clean up all notifications
        Notification::where('data->invoice_id', $invoice->id)->delete();
    }
}
