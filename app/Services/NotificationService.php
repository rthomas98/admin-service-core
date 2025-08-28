<?php

namespace App\Services;

use App\Enums\NotificationCategory;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\EmergencyService;
use App\Mail\GenericNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class NotificationService
{
    protected ?SmsService $smsService;

    public function __construct()
    {
        $this->smsService = app(SmsService::class);
    }

    /**
     * Send a notification immediately
     */
    public function send(Notification $notification): bool
    {
        try {
            if (!$notification->shouldSendNow()) {
                return false;
            }

            // Check recipient preferences
            if (!$this->checkRecipientPreferences($notification)) {
                $notification->markAsFailed('Recipient has disabled this type of notification');
                return false;
            }

            $result = match ($notification->type) {
                NotificationType::EMAIL => $this->sendEmail($notification),
                NotificationType::SMS => $this->sendSms($notification),
                NotificationType::PUSH => $this->sendPush($notification),
                NotificationType::IN_APP => true, // In-app notifications are just stored
                default => false,
            };

            if ($result) {
                $notification->markAsSent();
                return true;
            } else {
                $notification->markAsFailed('Failed to send notification');
                return false;
            }
        } catch (Exception $e) {
            Log::error('Notification sending failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            $notification->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(Notification $notification): bool
    {
        try {
            if (!$notification->recipient_email) {
                return false;
            }

            Mail::to($notification->recipient_email)
                ->send(new GenericNotification($notification));

            return true;
        } catch (Exception $e) {
            Log::error('Email sending failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send SMS notification
     */
    protected function sendSms(Notification $notification): bool
    {
        try {
            if (!$notification->recipient_phone || !$this->smsService) {
                return false;
            }

            return $this->smsService->send(
                $notification->recipient_phone,
                $notification->message
            );
        } catch (Exception $e) {
            Log::error('SMS sending failed', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send push notification (placeholder for future implementation)
     */
    protected function sendPush(Notification $notification): bool
    {
        // TODO: Implement push notification sending
        return false;
    }

    /**
     * Check if recipient has enabled this type of notification
     */
    protected function checkRecipientPreferences(Notification $notification): bool
    {
        // TODO: Implement preference checking
        return true;
    }

    /**
     * Create and send a notification from template
     */
    public function sendFromTemplate(
        string $templateSlug,
        $recipient,
        array $data = [],
        ?NotificationType $type = null,
        ?\DateTime $scheduledAt = null
    ): ?Notification {
        try {
            $template = NotificationTemplate::where('slug', $templateSlug)
                ->active()
                ->first();

            if (!$template) {
                Log::warning('Notification template not found', ['slug' => $templateSlug]);
                return null;
            }

            $rendered = $template->render($data);
            
            $notification = $this->createNotification(
                recipient: $recipient,
                type: $type ?? $template->type,
                category: $template->category,
                subject: $rendered['subject'],
                message: $rendered['body'],
                data: $data,
                scheduledAt: $scheduledAt
            );

            if (!$scheduledAt) {
                $this->send($notification);
            }

            return $notification;
        } catch (Exception $e) {
            Log::error('Failed to send notification from template', [
                'template' => $templateSlug,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Create a notification record
     */
    public function createNotification(
        $recipient,
        NotificationType $type,
        NotificationCategory $category,
        string $subject,
        string $message,
        array $data = [],
        ?\DateTime $scheduledAt = null
    ): Notification {
        $recipientData = $this->getRecipientData($recipient);

        return Notification::create([
            'company_id' => $recipient->company_id ?? auth()->user()->company_id,
            'type' => $type,
            'channel' => $this->determineChannel($recipient),
            'category' => $category,
            'recipient_type' => $recipientData['type'],
            'recipient_id' => $recipientData['id'],
            'recipient_email' => $recipientData['email'],
            'recipient_phone' => $recipientData['phone'],
            'subject' => $subject,
            'message' => $message,
            'data' => $data,
            'status' => $scheduledAt ? NotificationStatus::SCHEDULED : NotificationStatus::PENDING,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    /**
     * Get recipient data based on model type
     */
    protected function getRecipientData($recipient): array
    {
        return match (true) {
            $recipient instanceof Customer => [
                'type' => 'customer',
                'id' => $recipient->id,
                'email' => $recipient->email,
                'phone' => $recipient->phone,
            ],
            $recipient instanceof Driver => [
                'type' => 'driver',
                'id' => $recipient->id,
                'email' => $recipient->email,
                'phone' => $recipient->phone,
            ],
            $recipient instanceof User => [
                'type' => 'user',
                'id' => $recipient->id,
                'email' => $recipient->email,
                'phone' => null, // Add phone field to users table if needed
            ],
            default => [
                'type' => 'unknown',
                'id' => $recipient->id ?? 0,
                'email' => null,
                'phone' => null,
            ],
        };
    }

    /**
     * Determine the channel based on recipient type
     */
    protected function determineChannel($recipient): string
    {
        return match (true) {
            $recipient instanceof Customer => 'customer',
            $recipient instanceof Driver => 'driver',
            $recipient instanceof User => 'admin',
            default => 'unknown',
        };
    }

    // Specific notification methods

    /**
     * Send service reminder notification
     */
    public function sendServiceReminder(WorkOrder $workOrder): void
    {
        if ($workOrder->customer) {
            $this->sendFromTemplate(
                'service-reminder',
                $workOrder->customer,
                [
                    'customer_name' => $workOrder->customer->name,
                    'service_date' => $workOrder->service_date->format('l, F j, Y'),
                    'service_time' => $workOrder->time_period,
                    'service_type' => $workOrder->action->label(),
                    'address' => $workOrder->location,
                ]
            );
        }
    }

    /**
     * Send payment due reminder
     */
    public function sendPaymentReminder(Invoice $invoice): void
    {
        if ($invoice->customer) {
            $this->sendFromTemplate(
                'payment-due',
                $invoice->customer,
                [
                    'customer_name' => $invoice->customer->name,
                    'invoice_number' => $invoice->invoice_number,
                    'amount_due' => number_format($invoice->amount_due, 2),
                    'due_date' => $invoice->due_date->format('F j, Y'),
                ]
            );
        }
    }

    /**
     * Send driver dispatch notification
     */
    public function sendDriverDispatch(WorkOrder $workOrder): void
    {
        if ($workOrder->driver) {
            $this->sendFromTemplate(
                'driver-dispatch',
                $workOrder->driver,
                [
                    'driver_name' => $workOrder->driver->name,
                    'customer_name' => $workOrder->customer->name ?? 'N/A',
                    'service_date' => $workOrder->service_date->format('l, F j, Y'),
                    'service_time' => $workOrder->time_period,
                    'service_type' => $workOrder->action->label(),
                    'address' => $workOrder->location,
                    'special_instructions' => $workOrder->special_instructions,
                ],
                NotificationType::SMS
            );
        }
    }

    /**
     * Send emergency alert
     */
    public function sendEmergencyAlert(EmergencyService $emergency): void
    {
        // Send to all active drivers
        $drivers = Driver::active()->get();
        
        foreach ($drivers as $driver) {
            $this->sendFromTemplate(
                'emergency-alert',
                $driver,
                [
                    'location' => $emergency->location,
                    'service_type' => $emergency->service_type,
                    'priority' => $emergency->priority,
                    'contact_name' => $emergency->contact_name,
                    'contact_phone' => $emergency->contact_phone,
                ],
                NotificationType::SMS
            );
        }
    }

    /**
     * Send payment confirmation
     */
    public function sendPaymentConfirmation(Payment $payment): void
    {
        if ($payment->invoice && $payment->invoice->customer) {
            $this->sendFromTemplate(
                'payment-confirmation',
                $payment->invoice->customer,
                [
                    'customer_name' => $payment->invoice->customer->name,
                    'payment_amount' => number_format($payment->amount, 2),
                    'payment_date' => $payment->payment_date->format('F j, Y'),
                    'payment_method' => $payment->payment_method,
                    'invoice_number' => $payment->invoice->invoice_number,
                    'remaining_balance' => number_format($payment->invoice->amount_due - $payment->amount, 2),
                ]
            );
        }
    }

    /**
     * Process scheduled notifications
     */
    public function processScheduledNotifications(): int
    {
        $notifications = Notification::dueForSending()->get();
        $count = 0;

        foreach ($notifications as $notification) {
            if ($this->send($notification)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Retry failed notifications
     */
    public function retryFailedNotifications(): int
    {
        $notifications = Notification::failed()
            ->where('retry_count', '<', 3)
            ->get();
        
        $count = 0;

        foreach ($notifications as $notification) {
            if ($notification->canRetry()) {
                $notification->status = NotificationStatus::PENDING;
                $notification->save();
                
                if ($this->send($notification)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}