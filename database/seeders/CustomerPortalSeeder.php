<?php

namespace Database\Seeders;

use App\Enums\ServiceRequestStatus;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerInvite;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerPortalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first company and user for testing
        $company = Company::first();
        $user = User::first();

        if (! $company || ! $user) {
            $this->command->error('Please ensure you have at least one company and user in the database before running this seeder.');

            return;
        }

        // Create test customers with portal access
        $customers = [
            [
                'company_id' => $company->id,
                'name' => 'John Smith',
                'organization' => 'Smith Logistics LLC',
                'emails' => '["john.smith@smithlogistics.com"]',
                'phone' => '555-0001',
                'address' => '123 Main Street',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62701',
                'customer_since' => now()->subYear(),
                'portal_access' => true,
                'portal_password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'notifications_enabled' => true,
                'preferred_notification_method' => 'email',
            ],
            [
                'company_id' => $company->id,
                'name' => 'Sarah Johnson',
                'organization' => 'Johnson Construction',
                'emails' => '["sarah@johnsonconstruction.com"]',
                'phone' => '555-0002',
                'address' => '456 Oak Avenue',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62702',
                'customer_since' => now()->subMonths(6),
                'portal_access' => true,
                'portal_password' => bcrypt('password123'),
                'email_verified_at' => now(),
                'notifications_enabled' => true,
                'preferred_notification_method' => 'email',
            ],
            [
                'company_id' => $company->id,
                'name' => 'Mike Wilson',
                'organization' => 'Wilson Manufacturing',
                'emails' => '["mike.wilson@wilsonmfg.com"]',
                'phone' => '555-0003',
                'address' => '789 Industrial Drive',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62703',
                'customer_since' => now()->subMonths(3),
                'portal_access' => false, // No portal access yet
                'notifications_enabled' => true,
                'preferred_notification_method' => 'email',
            ],
        ];

        foreach ($customers as $customerData) {
            $customer = Customer::create($customerData);

            // Create some service requests for customers with portal access
            if ($customer->portal_access) {
                $this->createServiceRequestsForCustomer($customer, $company, $user);
            }
        }

        // Create some customer invites
        $this->createCustomerInvites($company, $user);

        $this->command->info('Customer portal test data created successfully!');
        $this->command->info('Test customer credentials:');
        $this->command->info('- john.smith@smithlogistics.com / password123');
        $this->command->info('- sarah@johnsonconstruction.com / password123');
    }

    private function createServiceRequestsForCustomer(Customer $customer, Company $company, User $user): void
    {
        $serviceRequests = [
            [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'title' => 'Regular Pickup Service',
                'description' => 'Weekly waste pickup for our facility',
                'status' => ServiceRequestStatus::COMPLETED,
                'priority' => 'medium',
                'category' => 'waste_management',
                'requested_date' => now()->subWeeks(2),
                'scheduled_date' => now()->subWeek(),
                'completed_date' => now()->subDays(5),
                'assigned_to' => $user->id,
                'customer_notes' => 'Please use the back entrance for pickup',
                'internal_notes' => 'Customer prefers morning pickups',
                'estimated_cost' => 150.00,
                'actual_cost' => 145.00,
            ],
            [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'title' => 'Emergency Cleanup Service',
                'description' => 'Need urgent cleanup after construction spill',
                'status' => ServiceRequestStatus::IN_PROGRESS,
                'priority' => 'high',
                'category' => 'emergency',
                'requested_date' => now()->subDays(2),
                'scheduled_date' => now()->addDay(),
                'assigned_to' => $user->id,
                'customer_notes' => 'Hazardous material cleanup required',
                'internal_notes' => 'Requires special equipment and certified technicians',
                'estimated_cost' => 500.00,
            ],
            [
                'customer_id' => $customer->id,
                'company_id' => $company->id,
                'title' => 'Monthly Dumpster Rental',
                'description' => 'Need 30-yard dumpster for ongoing project',
                'status' => ServiceRequestStatus::PENDING,
                'priority' => 'low',
                'category' => 'rental',
                'requested_date' => now(),
                'scheduled_date' => now()->addWeek(),
                'customer_notes' => 'Flexible on delivery date',
                'estimated_cost' => 300.00,
            ],
        ];

        foreach ($serviceRequests as $requestData) {
            ServiceRequest::create($requestData);
        }
    }

    private function createCustomerInvites(Company $company, User $user): void
    {
        $invites = [
            [
                'email' => 'mike.wilson@wilsonmfg.com',
                'customer_id' => Customer::where('emails', 'like', '%mike.wilson@wilsonmfg.com%')->first()?->id,
                'company_id' => $company->id,
                'invited_by' => $user->id,
                'expires_at' => now()->addDays(7),
            ],
            [
                'email' => 'newcustomer@example.com',
                'company_id' => $company->id,
                'invited_by' => $user->id,
                'expires_at' => now()->addDays(7),
            ],
            [
                'email' => 'expired@example.com',
                'company_id' => $company->id,
                'invited_by' => $user->id,
                'expires_at' => now()->subDay(), // Expired invite
            ],
        ];

        foreach ($invites as $inviteData) {
            CustomerInvite::create($inviteData);
        }
    }
}
