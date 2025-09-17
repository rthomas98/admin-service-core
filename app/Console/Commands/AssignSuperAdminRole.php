<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignSuperAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-super-admin {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign super_admin role to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (! $email) {
            // Get the first user if no email provided
            $user = User::first();
            if (! $user) {
                $this->error('No users found in the database.');

                return Command::FAILURE;
            }
        } else {
            $user = User::where('email', $email)->first();
            if (! $user) {
                $this->error("User with email {$email} not found.");

                return Command::FAILURE;
            }
        }

        // Check if super_admin role exists
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if (! $superAdminRole) {
            $this->error('Super admin role does not exist. Run php artisan db:seed --class=RolesAndPermissionsSeeder first.');

            return Command::FAILURE;
        }

        // Assign the role
        $user->assignRole('super_admin');

        $this->info("Super admin role assigned to {$user->name} ({$user->email})");

        return Command::SUCCESS;
    }
}
