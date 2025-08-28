<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class ProcessNotifications extends Command
{
    protected $signature = 'notifications:process 
                            {--retry : Also retry failed notifications}';

    protected $description = 'Process scheduled notifications and optionally retry failed ones';

    public function handle(NotificationService $notificationService): int
    {
        $this->info('Processing scheduled notifications...');
        
        $scheduled = $notificationService->processScheduledNotifications();
        $this->info("Sent {$scheduled} scheduled notifications.");
        
        if ($this->option('retry')) {
            $this->info('Retrying failed notifications...');
            $failed = $notificationService->retryFailedNotifications();
            $this->info("Retried {$failed} failed notifications.");
        }
        
        return Command::SUCCESS;
    }
}