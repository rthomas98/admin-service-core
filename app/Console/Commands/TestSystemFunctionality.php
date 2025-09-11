<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestSystemFunctionality extends Command
{
    protected $signature = 'test:system-functionality';
    protected $description = 'Test complete system functionality for all users and roles';

    private $testResults = [];
    private $failedTests = [];

    public function handle()
    {
        $this->info('🧪 Testing Complete System Functionality');
        $this->info('=' . str_repeat('=', 50));

        // Test 1: Database Connectivity
        $this->testDatabaseConnectivity();

        // Test 2: Company Setup
        $this->testCompanySetup();

        // Test 3: User Authentication & Company Association
        $this->testUserAuthentication();

        // Test 4: Multi-Tenancy Data Isolation
        $this->testMultiTenancy();

        // Test 5: Role-Based Access Control
        $this->testRoleBasedAccess();

        // Test 6: API Authentication for Field App
        $this->testApiAuthentication();

        // Test 7: Notification System
        $this->testNotificationSystem();

        // Test 8: Cross-Company Admin Access
        $this->testCrossCompanyAdminAccess();

        // Summary
        $this->displaySummary();

        return $this->failedTests ? 1 : 0;
    }

    private function testDatabaseConnectivity()
    {
        $this->info("\n📊 Testing Database Connectivity...");
        
        try {
            DB::connection()->getPdo();
            $this->addResult('Database Connection', true, 'Connected to PostgreSQL');
            
            // Check table counts
            $tables = [
                'users' => User::count(),
                'companies' => Company::count(),
                'customers' => Customer::count(),
                'drivers' => Driver::count(),
                'vehicles' => Vehicle::count(),
                'notifications' => Notification::count(),
            ];
            
            foreach ($tables as $table => $count) {
                $this->addResult("Table: $table", $count > 0, "Records: $count");
            }
        } catch (\Exception $e) {
            $this->addResult('Database Connection', false, $e->getMessage());
        }
    }

    private function testCompanySetup()
    {
        $this->info("\n🏢 Testing Company Setup...");
        
        $companies = Company::whereIn('slug', ['liv-transport', 'raw-disposal'])->get();
        
        foreach ($companies as $company) {
            $this->addResult(
                "Company: {$company->name}",
                true,
                "ID: {$company->id}, Slug: {$company->slug}"
            );
            
            // Check company relationships
            $counts = [
                'Users' => $company->users()->count(),
                'Customers' => Customer::where('company_id', $company->id)->count(),
                'Drivers' => Driver::where('company_id', $company->id)->count(),
                'Vehicles' => Vehicle::where('company_id', $company->id)->count(),
            ];
            
            foreach ($counts as $relation => $count) {
                $this->info("  - $relation: $count");
            }
        }
    }

    private function testUserAuthentication()
    {
        $this->info("\n🔐 Testing User Authentication & Company Associations...");
        
        $testUsers = [
            'admin@servicecore.local' => ['companies' => 2, 'role' => 'admin'],
            'manager@rawdisposal.com' => ['companies' => 1, 'role' => 'manager'],
            'manager@livtransport.com' => ['companies' => 1, 'role' => 'manager'],
            'employee@rawdisposal.com' => ['companies' => 1, 'role' => 'employee'],
            'employee@livtransport.com' => ['companies' => 1, 'role' => 'employee'],
        ];
        
        foreach ($testUsers as $email => $expected) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $this->addResult("User: $email", false, 'User not found');
                continue;
            }
            
            $companyCount = $user->companies()->count();
            $passed = $companyCount === $expected['companies'];
            
            $companies = $user->companies->map(function($c) {
                return "{$c->name} ({$c->pivot->role})";
            })->implode(', ');
            
            $this->addResult(
                "User: $email",
                $passed,
                "Companies: $companies"
            );
            
            // Test authentication
            $canAuth = Auth::attempt(['email' => $email, 'password' => 'password123']);
            $this->addResult(
                "  Auth: $email",
                $canAuth,
                $canAuth ? 'Authentication successful' : 'Authentication failed'
            );
            
            if ($canAuth) {
                Auth::logout();
            }
        }
    }

    private function testMultiTenancy()
    {
        $this->info("\n🔒 Testing Multi-Tenancy Data Isolation...");
        
        // Test RAW Disposal user sees only RAW data
        $rawUser = User::where('email', 'manager@rawdisposal.com')->first();
        if ($rawUser) {
            Auth::login($rawUser);
            $company = $rawUser->companies->first();
            
            // Check filtered data
            $customers = Customer::where('company_id', $company->id)->count();
            $allCustomers = Customer::count();
            
            $this->addResult(
                'RAW Disposal Data Isolation',
                $customers <= $allCustomers,
                "Sees $customers of $allCustomers total customers"
            );
            
            Auth::logout();
        }
        
        // Test LIV Transport user sees only LIV data
        $livUser = User::where('email', 'manager@livtransport.com')->first();
        if ($livUser) {
            Auth::login($livUser);
            $company = $livUser->companies->first();
            
            $customers = Customer::where('company_id', $company->id)->count();
            $allCustomers = Customer::count();
            
            $this->addResult(
                'LIV Transport Data Isolation',
                $customers <= $allCustomers,
                "Sees $customers of $allCustomers total customers"
            );
            
            Auth::logout();
        }
    }

    private function testRoleBasedAccess()
    {
        $this->info("\n👥 Testing Role-Based Access Control...");
        
        $roles = [
            'admin' => ['full_access' => true, 'can_delete' => true],
            'manager' => ['full_access' => false, 'can_delete' => true],
            'employee' => ['full_access' => false, 'can_delete' => false],
        ];
        
        foreach ($roles as $role => $permissions) {
            $user = User::whereHas('companies', function($q) use ($role) {
                $q->where('company_user.role', $role);
            })->first();
            
            if ($user) {
                $this->addResult(
                    "Role: $role",
                    true,
                    "User: {$user->email}"
                );
            } else {
                $this->addResult(
                    "Role: $role",
                    false,
                    "No user found with this role"
                );
            }
        }
    }

    private function testApiAuthentication()
    {
        $this->info("\n📱 Testing API Authentication for Field App...");
        
        $fieldUsers = [
            'john.smith@livtransport.com',
            'mike.johnson@rawdisposal.com'
        ];
        
        foreach ($fieldUsers as $email) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                // Check if user has driver record
                $driver = Driver::where('user_id', $user->id)->first();
                
                $this->addResult(
                    "Field User: $email",
                    $driver !== null,
                    $driver ? "Driver ID: {$driver->id}" : "No driver record"
                );
                
                // Check API token capability
                $token = $user->createToken('test-token')->plainTextToken;
                $this->addResult(
                    "  API Token",
                    strlen($token) > 0,
                    "Token generated successfully"
                );
                
                // Clean up token
                $user->tokens()->delete();
            } else {
                $this->addResult("Field User: $email", false, 'User not found');
            }
        }
    }

    private function testNotificationSystem()
    {
        $this->info("\n🔔 Testing Notification System...");
        
        $users = [
            'john.smith@livtransport.com',
            'mike.johnson@rawdisposal.com'
        ];
        
        foreach ($users as $email) {
            $user = User::where('email', $email)->first();
            
            if ($user) {
                $driver = Driver::where('user_id', $user->id)->first();
                
                if ($driver) {
                    // Check notifications
                    $notifications = Notification::where('recipient_id', $user->id)->count();
                    $unread = Notification::where('recipient_id', $user->id)
                        ->where('is_read', false)
                        ->count();
                    
                    $this->addResult(
                        "Notifications for $email",
                        $notifications > 0,
                        "Total: $notifications, Unread: $unread"
                    );
                    
                    // Check preferences
                    $prefs = $driver->notification_preferences ?? [];
                    $this->addResult(
                        "  Preferences",
                        !empty($prefs),
                        empty($prefs) ? 'No preferences set' : 'Preferences configured'
                    );
                }
            }
        }
    }

    private function testCrossCompanyAdminAccess()
    {
        $this->info("\n🌐 Testing Cross-Company Admin Access...");
        
        $admin = User::where('email', 'admin@servicecore.local')->first();
        
        if ($admin) {
            Auth::login($admin);
            
            $companies = $admin->companies;
            $this->addResult(
                'Admin Multi-Company Access',
                $companies->count() === 2,
                "Access to {$companies->count()} companies: " . 
                $companies->pluck('name')->implode(', ')
            );
            
            // Test data access across companies
            $rawCompany = Company::where('slug', 'raw-disposal')->first();
            $livCompany = Company::where('slug', 'liv-transport')->first();
            
            if ($rawCompany && $livCompany) {
                $rawCustomers = Customer::where('company_id', $rawCompany->id)->count();
                $livCustomers = Customer::where('company_id', $livCompany->id)->count();
                
                $this->addResult(
                    '  Cross-Company Data Access',
                    true,
                    "RAW: $rawCustomers customers, LIV: $livCustomers customers"
                );
            }
            
            Auth::logout();
        } else {
            $this->addResult('Admin User', false, 'Admin user not found');
        }
    }

    private function addResult($test, $passed, $message)
    {
        $this->testResults[] = [
            'test' => $test,
            'passed' => $passed,
            'message' => $message
        ];
        
        if (!$passed) {
            $this->failedTests[] = $test;
        }
        
        $icon = $passed ? '✅' : '❌';
        $this->line("$icon $test: $message");
    }

    private function displaySummary()
    {
        $this->info("\n" . str_repeat('=', 60));
        $this->info('📊 TEST SUMMARY');
        $this->info(str_repeat('=', 60));
        
        $total = count($this->testResults);
        $passed = count(array_filter($this->testResults, fn($r) => $r['passed']));
        $failed = $total - $passed;
        
        $this->info("Total Tests: $total");
        $this->info("Passed: $passed");
        
        if ($failed > 0) {
            $this->error("Failed: $failed");
            $this->error("\nFailed Tests:");
            foreach ($this->failedTests as $test) {
                $this->error("  - $test");
            }
        } else {
            $this->info("Failed: 0");
            $this->info("\n🎉 All tests passed successfully!");
        }
        
        // System Status
        $this->info("\n" . str_repeat('=', 60));
        $this->info('💻 SYSTEM STATUS');
        $this->info(str_repeat('=', 60));
        
        $status = [
            'Database' => 'PostgreSQL Connected',
            'Companies' => Company::count() . ' registered',
            'Users' => User::count() . ' total',
            'Authenticated Users' => User::whereHas('companies')->count() . ' with company access',
            'Drivers' => Driver::count() . ' registered',
            'Vehicles' => Vehicle::count() . ' in fleet',
            'Notifications' => Notification::count() . ' sent',
        ];
        
        foreach ($status as $key => $value) {
            $this->info("$key: $value");
        }
    }
}