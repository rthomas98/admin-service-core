<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;

class MorePageNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get John Smith's driver record or any driver
        $driver = Driver::whereHas('user', function($q) {
            $q->where('email', 'john.smith@livtransport.com');
        })->first();
        
        if (!$driver) {
            $driver = Driver::first();
        }
        
        if (!$driver) {
            $this->command->info('No drivers found. Please run FieldAppDriverSeeder first.');
            return;
        }
        
        $companyId = $driver->company_id;
        
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
        
        $notifications = [
            // Dispatch notifications
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'dispatch',
                'subject' => 'New Work Order Assigned',
                'message' => 'You have been assigned work order #WO-2024-1234 for Chicago delivery route.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'high',
                'is_read' => false,
                'action_data' => json_encode(['url' => '/work-orders/WO-2024-1234']),
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ],
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'delivery',
                'subject' => 'Delivery Route Updated',
                'message' => 'Your delivery route #DR-2024-1233 has been updated with new delivery instructions.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'medium',
                'is_read' => true,
                'read_at' => Carbon::now()->subHours(1),
                'action_data' => json_encode(['url' => '/routes/DR-2024-1233']),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            // Service reminders
            [
                'company_id' => $companyId,
                'type' => 'push',
                'channel' => 'push',
                'category' => 'service_reminder',
                'subject' => 'Schedule Change',
                'message' => 'Your shift tomorrow has been changed to start at 7:00 AM instead of 8:00 AM.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'high',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(4),
                'updated_at' => Carbon::now()->subHours(4),
            ],
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'dispatch',
                'subject' => 'Route Assignment for Tomorrow',
                'message' => 'You have been assigned to Route R-15 (North Side) for tomorrow\'s shift.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'medium',
                'is_read' => false,
                'action_data' => json_encode(['url' => '/routes/R-15']),
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ],
            // Maintenance notifications
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'maintenance',
                'subject' => 'Vehicle Maintenance Required',
                'message' => 'Vehicle #LIV-001 is due for oil change in 500 miles. Please schedule maintenance.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'medium',
                'is_read' => false,
                'action_data' => json_encode(['url' => '/vehicles/LIV-001/maintenance']),
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'company_id' => $companyId,
                'type' => 'push',
                'channel' => 'push',
                'category' => 'maintenance',
                'subject' => 'Pre-Trip Inspection Reminder',
                'message' => 'Remember to complete your pre-trip vehicle inspection before starting your route.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'low',
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(1),
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            // Emergency notifications
            [
                'company_id' => $companyId,
                'type' => 'push',
                'channel' => 'push',
                'category' => 'emergency',
                'subject' => 'Weather Alert',
                'message' => 'Heavy rain expected in your area today. Please drive carefully and allow extra time.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'critical',
                'is_read' => false,
                'created_at' => Carbon::now()->subHours(1),
                'updated_at' => Carbon::now()->subHours(1),
            ],
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'service_reminder',
                'subject' => 'Safety Training Due',
                'message' => 'Your annual safety training certification expires in 30 days. Please complete the online module.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'medium',
                'is_read' => false,
                'action_data' => json_encode(['url' => '/training/safety-annual']),
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            // System updates
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'system_update',
                'subject' => 'Holiday Schedule',
                'message' => 'Office will be closed on December 25th and January 1st. Emergency support will be available.',
                'recipient_id' => 0,
                'recipient_type' => 'all_drivers',
                'status' => 'sent',
                'priority' => 'low',
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(2),
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'system_update',
                'subject' => 'New Mobile App Features',
                'message' => 'The app has been updated with improved navigation and faster sync. Update now to enjoy the new features.',
                'recipient_id' => 0,
                'recipient_type' => 'all_drivers',
                'status' => 'sent',
                'priority' => 'low',
                'is_read' => false,
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(4),
            ],
            // Payment and document notifications
            [
                'company_id' => $companyId,
                'type' => 'email',
                'channel' => 'email',
                'category' => 'service_reminder',
                'subject' => 'Medical Certificate Expiring',
                'message' => 'Your DOT medical certificate will expire in 15 days. Please renew and upload the new certificate.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'high',
                'is_read' => false,
                'action_data' => json_encode(['url' => '/documents/medical-certificate']),
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'company_id' => $companyId,
                'type' => 'in_app',
                'channel' => 'in_app',
                'category' => 'service_reminder',
                'subject' => 'Insurance Card Updated',
                'message' => 'Your vehicle insurance card has been updated. The new card is available in your documents.',
                'recipient_id' => $driver->user_id,
                'recipient_type' => 'user',
                'status' => 'sent',
                'priority' => 'low',
                'is_read' => true,
                'read_at' => Carbon::now()->subDays(3),
                'action_data' => json_encode(['url' => '/documents/insurance']),
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::firstOrCreate(
                [
                    'company_id' => $notification['company_id'],
                    'subject' => $notification['subject'],
                    'recipient_id' => $notification['recipient_id'] ?? null,
                    'recipient_type' => $notification['recipient_type'],
                ],
                $notification
            );
        }

        $this->command->info('More page notifications seeded successfully!');
        $this->command->info('- Created ' . count($notifications) . ' notifications for driver');
        $this->command->info('- Updated driver notification preferences');
    }
}
