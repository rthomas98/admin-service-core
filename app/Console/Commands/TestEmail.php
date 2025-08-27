<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = $this->argument('email') ?? 'test@servicecore.local';
        
        $this->info('Sending test email to: ' . $recipient);
        
        try {
            Mail::raw('This is a test email from Service Core admin panel. If you receive this, your email configuration is working correctly!', function ($message) use ($recipient) {
                $message->to($recipient)
                        ->subject('Service Core - Test Email');
            });
            
            $this->info('✅ Test email sent successfully!');
            $this->info('Check your Herd mail viewer to see the email.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to send test email: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}