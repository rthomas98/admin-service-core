<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;

class TestPasswordReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:password-reset {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test password reset functionality by sending a reset link';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?? 'admin@servicecore.local';
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email {$email} not found!");
            return Command::FAILURE;
        }
        
        $this->info("Sending password reset link to: {$email}");
        
        $status = Password::sendResetLink(['email' => $email]);
        
        if ($status === Password::RESET_LINK_SENT) {
            $this->info('✅ Password reset link sent successfully!');
            $this->info('Check your Herd mail viewer to see the reset email.');
            return Command::SUCCESS;
        }
        
        $this->error('Failed to send password reset link: ' . $status);
        return Command::FAILURE;
    }
}