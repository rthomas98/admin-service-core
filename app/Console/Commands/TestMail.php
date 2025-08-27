<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test mail configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@rawdisposal.test';
        
        $this->info('🚀 Testing mail configuration...');
        $this->info('Sending test email to: ' . $email);
        
        try {
            Mail::raw('This is a test email from RAW Disposal to verify Herd Pro mail service is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - Herd Pro Mail Service');
            });
            
            $this->info('✅ Email sent successfully!');
            $this->info('');
            $this->info('📧 View your emails at: http://localhost:8025');
            $this->info('This is where Herd Pro captures all outgoing emails during development.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());
            $this->error('');
            $this->error('Please check your mail configuration:');
            $this->error('- MAIL_HOST should be: localhost');
            $this->error('- MAIL_PORT should be: 2525');
            $this->error('- Make sure Herd Pro services are running');
            
            return Command::FAILURE;
        }
    }
}
