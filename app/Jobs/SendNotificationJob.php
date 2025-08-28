<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // Retry after 60 seconds

    public function __construct(
        protected Notification $notification
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        try {
            $notificationService->send($this->notification);
            
            Log::info('Notification sent successfully', [
                'notification_id' => $this->notification->id,
                'type' => $this->notification->type,
                'recipient' => $this->notification->recipient_type . ':' . $this->notification->recipient_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Mark as failed if this was the last retry
            if ($this->attempts() >= $this->tries) {
                $this->notification->markAsFailed($e->getMessage());
            }
            
            throw $e; // Re-throw to trigger retry
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Notification job permanently failed', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);
        
        $this->notification->markAsFailed($exception->getMessage());
    }
}