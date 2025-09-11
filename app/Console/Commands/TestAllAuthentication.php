<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Driver;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class TestAllAuthentication extends Command
{
    protected $signature = 'test:auth';
    protected $description = 'Test all authentication endpoints and user roles';

    private $baseUrl = 'http://localhost:8000';
    private $testResults = [];

    public function handle()
    {
        $this->info('\n========================================');
        $this->info('   AUTHENTICATION SYSTEM TEST');
        $this->info('========================================\n');

        // Test Admin Panel Access
        $this->testAdminPanelAccess();

        // Test Field App API
        $this->testFieldAppAPI();

        // Display Results
        $this->displayResults();

        return Command::SUCCESS;
    }

    private function testAdminPanelAccess()
    {
        $this->info('🔍 Testing Admin Panel Access...');
        $this->newLine();

        $adminUsers = [
            ['email' => 'admin@servicecore.local', 'role' => 'Super Admin', 'companies' => ['LIV Transport', 'RAW Disposal']],
            ['email' => 'manager@livtransport.com', 'role' => 'LIV Manager', 'companies' => ['LIV Transport']],
            ['email' => 'manager@rawdisposal.com', 'role' => 'RAW Manager', 'companies' => ['RAW Disposal']],
        ];

        foreach ($adminUsers as $testUser) {
            $user = User::where('email', $testUser['email'])->first();
            
            if (!$user) {
                $this->error("   ❌ User not found: {$testUser['email']}");
                $this->testResults[] = ['type' => 'Admin', 'user' => $testUser['email'], 'status' => 'FAILED', 'reason' => 'User not found'];
                continue;
            }

            $companies = $user->companies;
            $companyNames = $companies->pluck('name')->toArray();
            
            $this->info("   📧 {$testUser['email']} ({$testUser['role']})");
            
            if ($companies->count() > 0) {
                $this->info("      ✅ Has access to: " . implode(', ', $companyNames));
                $this->testResults[] = ['type' => 'Admin', 'user' => $testUser['email'], 'status' => 'PASSED', 'companies' => $companyNames];
            } else {
                $this->error("      ❌ No company associations");
                $this->testResults[] = ['type' => 'Admin', 'user' => $testUser['email'], 'status' => 'FAILED', 'reason' => 'No companies'];
            }
        }
        $this->newLine();
    }

    private function testFieldAppAPI()
    {
        $this->info('🔍 Testing Field App API...');
        $this->newLine();

        $fieldUsers = [
            ['email' => 'john.smith@livtransport.com', 'company_id' => 3, 'company' => 'LIV Transport'],
            ['email' => 'mike.johnson@rawdisposal.com', 'company_id' => 1, 'company' => 'RAW Disposal'],
        ];

        foreach ($fieldUsers as $testUser) {
            $this->info("   📱 Testing {$testUser['email']}...");
            
            // Test companies endpoint
            try {
                $response = Http::post("{$this->baseUrl}/api/auth/companies", [
                    'email' => $testUser['email']
                ]);
                
                if ($response->successful()) {
                    $companies = $response->json()['companies'] ?? [];
                    if (count($companies) > 0) {
                        $this->info("      ✅ Companies endpoint: Found " . $companies[0]['name']);
                    } else {
                        $this->error("      ❌ Companies endpoint: No companies returned");
                        $this->testResults[] = ['type' => 'Field API', 'user' => $testUser['email'], 'status' => 'FAILED', 'reason' => 'No companies'];
                        continue;
                    }
                } else {
                    $this->error("      ❌ Companies endpoint failed");
                    $this->testResults[] = ['type' => 'Field API', 'user' => $testUser['email'], 'status' => 'FAILED', 'reason' => 'Companies endpoint failed'];
                    continue;
                }
            } catch (\Exception $e) {
                $this->error("      ❌ Companies endpoint error: " . $e->getMessage());
                continue;
            }
            
            // Test login endpoint
            try {
                $response = Http::post("{$this->baseUrl}/api/auth/login", [
                    'email' => $testUser['email'],
                    'password' => 'password',
                    'company_id' => $testUser['company_id'],
                    'device_name' => 'Test Device'
                ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['token'])) {
                        $this->info("      ✅ Login successful");
                        $this->info("         • Token: " . substr($data['token'], 0, 20) . "...");
                        $this->info("         • Driver: {$data['driver']['first_name']} {$data['driver']['last_name']}");
                        $this->info("         • Company: {$data['company']['name']}");
                        $this->testResults[] = ['type' => 'Field API', 'user' => $testUser['email'], 'status' => 'PASSED', 'company' => $data['company']['name']];
                    } else {
                        $this->error("      ❌ Login failed: No token returned");
                        $this->testResults[] = ['type' => 'Field API', 'user' => $testUser['email'], 'status' => 'FAILED', 'reason' => 'No token'];
                    }
                } else {
                    $error = $response->json()['message'] ?? 'Unknown error';
                    $this->error("      ❌ Login failed: {$error}");
                    $this->testResults[] = ['type' => 'Field API', 'user' => $testUser['email'], 'status' => 'FAILED', 'reason' => $error];
                }
            } catch (\Exception $e) {
                $this->error("      ❌ Login error: " . $e->getMessage());
                $this->testResults[] = ['type' => 'Field API', 'user' => $testUser['email'], 'status' => 'FAILED', 'reason' => $e->getMessage()];
            }
        }
        $this->newLine();
    }

    private function displayResults()
    {
        $this->info('========================================');
        $this->info('   TEST RESULTS SUMMARY');
        $this->info('========================================\n');

        $passed = collect($this->testResults)->where('status', 'PASSED')->count();
        $failed = collect($this->testResults)->where('status', 'FAILED')->count();
        $total = count($this->testResults);

        if ($failed === 0) {
            $this->info("🎉 All tests passed! ({$passed}/{$total})");
        } else {
            $this->warn("⚠️  Some tests failed: {$passed} passed, {$failed} failed");
            $this->newLine();
            $this->error('Failed Tests:');
            foreach ($this->testResults as $result) {
                if ($result['status'] === 'FAILED') {
                    $this->error("  • {$result['type']}: {$result['user']} - {$result['reason']}");
                }
            }
        }

        $this->newLine();
        $this->info('Test Credentials:');
        $this->info('  Admin Panel: All users use password "password"');
        $this->info('  Field App: john.smith@livtransport.com / password');
        $this->info('            mike.johnson@rawdisposal.com / password');
        $this->newLine();
    }
}