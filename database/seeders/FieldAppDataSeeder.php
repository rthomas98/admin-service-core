<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Driver;
use App\Models\WorkOrder;
use App\Models\DriverAssignment;
use App\Models\Vehicle;
use App\Models\Trailer;
use App\Models\Customer;
use App\Models\Notification;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderAction;
use App\Enums\TimePeriod;
use Carbon\Carbon;

class FieldAppDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get John Smith's driver record
        $user = User::where('email', 'john.smith@livtransport.com')->first();
        if (!$user) {
            $this->command->error('John Smith user not found. Run FieldAppDriverSeeder first.');
            return;
        }
        
        $driver = Driver::where('user_id', $user->id)->first();
        if (!$driver) {
            $this->command->error('John Smith driver record not found.');
            return;
        }
        
        $companyId = $driver->company_id;
        
        // Create or get a vehicle for assignments
        $vehicle = Vehicle::firstOrCreate(
            [
                'company_id' => $companyId,
                'unit_number' => 'LIV-001',
            ],
            [
                'make' => 'Freightliner',
                'model' => 'Cascadia',
                'year' => 2022,
                'license_plate' => 'IL-12345',
                'vin' => 'WDB9061351N123456',
                'odometer' => 45000,
                'fuel_type' => 'diesel',
                'type' => 'truck',
                'status' => 'active',
            ]
        );
        
        // Create a trailer
        $trailer = Trailer::firstOrCreate(
            [
                'company_id' => $companyId,
                'unit_number' => 'TRL-001',
            ],
            [
                'type' => 'dry_van',
                'make' => 'Great Dane',
                'model' => 'Champion',
                'year' => 2021,
                'license_plate' => 'IL-TR001',
                'vin' => 'TRL9061351N123456',
                'status' => 'active',
            ]
        );
        
        // Create some customers
        $customer1 = Customer::firstOrCreate(
            [
                'company_id' => $companyId,
                'organization' => 'ACME Corporation',
            ],
            [
                'name' => 'James Wilson',
                'first_name' => 'James',
                'last_name' => 'Wilson',
                'emails' => json_encode(['warehouse@acmecorp.com']),
                'phone' => '312-555-0101',
                'address' => '123 Industrial Way',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip' => '60601',
            ]
        );
        
        $customer2 = Customer::firstOrCreate(
            [
                'company_id' => $companyId,
                'organization' => 'Global Tech Solutions',
            ],
            [
                'name' => 'Sarah Johnson',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'emails' => json_encode(['shipping@globaltech.com']),
                'phone' => '847-555-0202',
                'address' => '456 Technology Drive',
                'city' => 'Schaumburg',
                'state' => 'IL',
                'zip' => '60173',
            ]
        );
        
        $customer3 = Customer::firstOrCreate(
            [
                'company_id' => $companyId,
                'organization' => 'Midwest Distribution',
            ],
            [
                'name' => 'Robert Brown',
                'first_name' => 'Robert',
                'last_name' => 'Brown',
                'emails' => json_encode(['logistics@midwest.com']),
                'phone' => '708-555-0303',
                'address' => '789 Logistics Parkway',
                'city' => 'Oak Brook',
                'state' => 'IL',
                'zip' => '60523',
            ]
        );
        
        // Create today's work orders
        $today = Carbon::today();
        
        // Completed work order from this morning
        WorkOrder::create([
            'company_id' => $companyId,
            'driver_id' => $driver->id,
            'customer_id' => $customer1->id,
            'ticket_number' => 'WO-' . date('Ymd') . '-001',
            'po_number' => 'PO-2024-1001',
            'service_date' => $today,
            'time_on_site' => Carbon::parse('08:00'),
            'time_off_site' => Carbon::parse('09:30'),
            'time_on_site_period' => TimePeriod::AM,
            'time_off_site_period' => TimePeriod::AM,
            'status' => WorkOrderStatus::COMPLETED,
            'action' => WorkOrderAction::DELIVERY,
            'customer_name' => $customer1->organization,
            'address' => $customer1->address,
            'city' => $customer1->city,
            'state' => $customer1->state,
            'zip' => $customer1->zip,
            'container_size' => '20 yard',
            'waste_type' => 'Construction Debris',
            'service_description' => 'Delivered 20 yard container for construction project',
            'completed_at' => Carbon::parse('09:30'),
            'truck_number' => $vehicle->unit_number,
        ]);
        
        // In progress work order
        WorkOrder::create([
            'company_id' => $companyId,
            'driver_id' => $driver->id,
            'customer_id' => $customer2->id,
            'ticket_number' => 'WO-' . date('Ymd') . '-002',
            'po_number' => 'PO-2024-1002',
            'service_date' => $today,
            'time_on_site' => Carbon::parse('10:30'),
            'time_on_site_period' => TimePeriod::AM,
            'status' => WorkOrderStatus::IN_PROGRESS,
            'action' => WorkOrderAction::SERVICE,
            'customer_name' => $customer2->organization,
            'address' => $customer2->address,
            'city' => $customer2->city,
            'state' => $customer2->state,
            'zip' => $customer2->zip,
            'container_size' => '30 yard',
            'waste_type' => 'Mixed Waste',
            'service_description' => 'Swap 30 yard container - mixed commercial waste',
            'truck_number' => $vehicle->unit_number,
        ]);
        
        // Upcoming work orders for today
        WorkOrder::create([
            'company_id' => $companyId,
            'driver_id' => $driver->id,
            'customer_id' => $customer3->id,
            'ticket_number' => 'WO-' . date('Ymd') . '-003',
            'po_number' => 'PO-2024-1003',
            'service_date' => $today,
            'time_on_site' => Carbon::parse('13:00'),
            'time_on_site_period' => TimePeriod::PM,
            'status' => WorkOrderStatus::SCHEDULED,
            'action' => WorkOrderAction::PICKUP,
            'customer_name' => $customer3->organization,
            'address' => $customer3->address,
            'city' => $customer3->city,
            'state' => $customer3->state,
            'zip' => $customer3->zip,
            'container_size' => '40 yard',
            'waste_type' => 'Recyclables',
            'service_description' => 'Pickup 40 yard container with recyclable materials',
            'truck_number' => $vehicle->unit_number,
        ]);
        
        WorkOrder::create([
            'company_id' => $companyId,
            'driver_id' => $driver->id,
            'customer_id' => $customer1->id,
            'ticket_number' => 'WO-' . date('Ymd') . '-004',
            'po_number' => 'PO-2024-1004',
            'service_date' => $today,
            'time_on_site' => Carbon::parse('15:00'),
            'time_on_site_period' => TimePeriod::PM,
            'status' => WorkOrderStatus::SCHEDULED,
            'action' => WorkOrderAction::DELIVERY,
            'customer_name' => $customer1->organization,
            'address' => '890 North Avenue',
            'city' => 'Naperville',
            'state' => 'IL',
            'zip' => '60563',
            'container_size' => '20 yard',
            'waste_type' => 'Concrete',
            'service_description' => 'Deliver 20 yard container for concrete disposal',
            'truck_number' => $vehicle->unit_number,
        ]);
        
        // Create an active driver assignment
        DriverAssignment::create([
            'company_id' => $companyId,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'trailer_id' => $trailer->id,
            'status' => 'active',
            'route' => 'Chicago Metro Route',
            'origin' => 'LIV Transport Yard - Chicago',
            'destination' => 'Various Locations - Chicago Area',
            'start_date' => $today->copy()->setTime(6, 0),
            'cargo_type' => 'Waste Containers',
            'expected_duration_hours' => 10,
            'mileage_start' => 45000,
            'notes' => 'Daily waste collection and container service route',
        ]);
        
        // Create tomorrow's work orders
        $tomorrow = Carbon::tomorrow();
        
        WorkOrder::create([
            'company_id' => $companyId,
            'driver_id' => $driver->id,
            'customer_id' => $customer2->id,
            'ticket_number' => 'WO-' . $tomorrow->format('Ymd') . '-001',
            'po_number' => 'PO-2024-1005',
            'service_date' => $tomorrow,
            'time_on_site' => Carbon::parse('09:00'),
            'time_on_site_period' => TimePeriod::AM,
            'status' => WorkOrderStatus::SCHEDULED,
            'action' => WorkOrderAction::DELIVERY,
            'customer_name' => $customer2->organization,
            'address' => $customer2->address,
            'city' => $customer2->city,
            'state' => $customer2->state,
            'zip' => $customer2->zip,
            'container_size' => '10 yard',
            'waste_type' => 'Office Waste',
            'service_description' => 'Deliver 10 yard container for office cleanout',
            'truck_number' => $vehicle->unit_number,
        ]);
        
        // Seed notifications for the More page features
        $this->seedNotifications($driver, $companyId);
        
        // Update driver preferences
        $driver->update([
            'notification_preferences' => [
                'email_notifications' => true,
                'push_notifications' => true,
                'sms_notifications' => false,
                'work_order_updates' => true,
                'schedule_changes' => true,
                'maintenance_reminders' => true,
                'safety_alerts' => true,
                'company_announcements' => true,
            ]
        ]);
        
        $this->command->info('Field app data seeded successfully!');
        $this->command->info('- Created 6 work orders (1 completed, 1 in progress, 4 scheduled)');
        $this->command->info('- Created 1 active driver assignment');
        $this->command->info('- Created vehicle LIV-001 and trailer TRL-001');
        $this->command->info('- Created 3 customers');
        $this->command->info('- Created notifications and updated driver preferences');
    }
    
    private function seedNotifications($driver, $companyId): void
    {
        $notifications = [
            // Work order notifications
            [
                'company_id' => $companyId,
                'type' => 'work_order',
                'category' => 'work_order',
                'title' => 'New Work Order Assigned',
                'message' => 'You have been assigned work order #WO-2024-1234 for Chicago delivery route.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'high',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'company_id' => $companyId,
                'type' => 'work_order',
                'category' => 'work_order',
                'title' => 'Work Order Updated',
                'message' => 'Work order #WO-2024-1233 has been updated with new delivery instructions.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'medium',
                'is_read' => true,
                'read_at' => Carbon::now()->subHours(1),
                'created_at' => Carbon::now()->subDays(1),
            ],
            // Schedule notifications
            [
                'company_id' => $companyId,
                'type' => 'schedule',
                'category' => 'schedule',
                'title' => 'Schedule Change',
                'message' => 'Your shift tomorrow has been changed to start at 7:00 AM instead of 8:00 AM.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'high',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(4),
            ],
            // Maintenance notifications
            [
                'company_id' => $companyId,
                'type' => 'maintenance',
                'category' => 'maintenance',
                'title' => 'Vehicle Maintenance Required',
                'message' => 'Vehicle #LIV-001 is due for oil change in 500 miles. Please schedule maintenance.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'medium',
                'is_read' => false,
                'created_at' => Carbon::now()->subDays(2),
            ],
            // Safety notifications
            [
                'company_id' => $companyId,
                'type' => 'safety',
                'category' => 'safety',
                'title' => 'Weather Alert',
                'message' => 'Heavy rain expected in your area today. Please drive carefully and allow extra time.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'high',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(1),
            ],
            // Company announcements
            [
                'company_id' => $companyId,
                'type' => 'announcement',
                'category' => 'company',
                'title' => 'Holiday Schedule',
                'message' => 'Office will be closed on December 25th and January 1st. Emergency support will be available.',
                'recipient_type' => 'all_drivers',
                'status' => 'sent',
                'priority' => 'low',
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(7),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::firstOrCreate(
                [
                    'company_id' => $notification['company_id'],
                    'title' => $notification['title'],
                    'recipient_id' => $notification['recipient_id'] ?? null,
                    'recipient_type' => $notification['recipient_type'],
                ],
                $notification
            );
        }
    }
}