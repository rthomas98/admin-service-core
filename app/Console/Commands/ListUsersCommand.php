<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsersCommand extends Command
{
    protected $signature = 'users:list';
    protected $description = 'List all users and their company assignments';

    public function handle(): int
    {
        $this->info('System Users & Company Assignments');
        $this->info('===================================');
        
        $users = User::with('companies')->orderBy('name')->get();
        
        if ($users->isEmpty()) {
            $this->warn('No users found in the system.');
            return Command::SUCCESS;
        }
        
        $tableData = [];
        
        foreach ($users as $user) {
            $companies = $user->companies->map(function($company) {
                return "{$company->name} ({$company->pivot->role})";
            })->join(', ');
            
            $tableData[] = [
                'Name' => $user->name,
                'Email' => $user->email,
                'Companies' => $companies ?: 'No companies assigned',
                'Verified' => $user->email_verified_at ? '✅' : '❌',
            ];
        }
        
        $this->table(
            ['Name', 'Email', 'Companies', 'Verified'],
            $tableData
        );
        
        $this->info("\nTotal users: " . $users->count());
        
        // Show login credentials reminder
        $this->info("\n💡 Default Password: password123");
        $this->info("📍 Login URL: http://admin-service-core.test/admin/login");
        
        return Command::SUCCESS;
    }
}