<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-super-admin {--email=rob.thomas@empuls3.com} {--name=Rob Thomas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update super admin account for production';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $name = $this->option('name');

        // Get password from environment or prompt for it
        $password = env('SUPER_ADMIN_PASSWORD');

        if (!$password) {
            $this->error('SUPER_ADMIN_PASSWORD environment variable is not set!');
            $this->info('Please set SUPER_ADMIN_PASSWORD in your .env file or Laravel Cloud environment variables.');

            if ($this->confirm('Would you like to set a password now?')) {
                $password = $this->secret('Enter password for super admin');
                $confirmPassword = $this->secret('Confirm password');

                if ($password !== $confirmPassword) {
                    $this->error('Passwords do not match!');
                    return 1;
                }
            } else {
                return 1;
            }
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            $this->info("✅ Super admin account created successfully!");
        } else {
            $this->info("✅ Super admin account updated successfully!");
        }

        $this->info("Email: {$email}");
        $this->info("Name: {$name}");
        $this->line("Password: [Set via SUPER_ADMIN_PASSWORD environment variable]");

        return 0;
    }
}
