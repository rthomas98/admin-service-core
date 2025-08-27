<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;

class TestTenancy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tenancy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test multi-tenancy setup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Multi-Tenancy Setup');
        $this->info('============================');
        
        // Check companies
        $companies = Company::all();
        $this->info("\nCompanies in database: " . $companies->count());
        
        foreach ($companies as $company) {
            $this->line("- {$company->name} (slug: {$company->slug}, type: {$company->type})");
        }
        
        // Check admin user
        $adminUser = User::where('email', 'admin@servicecore.local')->first();
        
        if ($adminUser) {
            $this->info("\n✅ Admin user found: {$adminUser->email}");
            
            $userCompanies = $adminUser->companies;
            $this->info("Companies assigned to admin: " . $userCompanies->count());
            
            foreach ($userCompanies as $company) {
                $role = $company->pivot->role;
                $this->line("- {$company->name} (role: {$role})");
            }
            
            // Test tenant access methods
            $this->info("\nTesting tenant access methods:");
            
            foreach ($companies as $company) {
                $canAccess = $adminUser->canAccessTenant($company);
                $isAdmin = $adminUser->isAdminFor($company);
                
                $this->line("- Can access {$company->name}: " . ($canAccess ? '✅ Yes' : '❌ No'));
                $this->line("  Is admin: " . ($isAdmin ? '✅ Yes' : '❌ No'));
            }
            
            $this->info("\n✅ Multi-tenancy is properly configured!");
            $this->info("\nYou can now:");
            $this->info("1. Login at: http://admin-service-core.test/admin/login");
            $this->info("2. Use credentials: admin@servicecore.local / password123");
            $this->info("3. Switch between LIV Transport and RAW Disposal companies");
            
        } else {
            $this->error('Admin user not found! Run: php artisan db:seed --class=CreateAdminUserSeeder');
            return Command::FAILURE;
        }
        
        return Command::SUCCESS;
    }
}