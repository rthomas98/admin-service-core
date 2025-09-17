<?php

namespace Database\Seeders;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // Service Reminders
            [
                'name' => 'Service Reminder',
                'slug' => 'service-reminder',
                'category' => NotificationCategory::SERVICE_REMINDER,
                'available_types' => [NotificationType::EMAIL, NotificationType::SMS],
                'subject' => 'Upcoming Waste Collection Service',
                'email_body' => '<h2>Service Reminder</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>This is a reminder that your waste collection service is scheduled for <strong>{{service_date}}</strong> at <strong>{{service_time}}</strong>.</p>
                    <p>Please ensure your bins are placed at the collection point before the scheduled time.</p>
                    <p>Service Details:</p>
                    <ul>
                        <li>Service Type: {{service_type}}</li>
                        <li>Address: {{service_address}}</li>
                    </ul>
                    <p>Thank you for choosing {{company_name}}!</p>',
                'sms_body' => 'Hi {{customer_name}}, reminder: Your {{service_type}} is scheduled for {{service_date}} at {{service_time}}. Please have bins ready. - {{company_name}}',
                'variables' => ['customer_name', 'service_date', 'service_time', 'service_type', 'service_address', 'company_name'],
                'is_active' => true,
            ],

            // Payment Reminders
            [
                'name' => 'Payment Due Reminder',
                'slug' => 'payment-due',
                'category' => NotificationCategory::PAYMENT_DUE,
                'available_types' => [NotificationType::EMAIL, NotificationType::SMS],
                'subject' => 'Payment Reminder - Invoice #{{invoice_number}}',
                'email_body' => '<h2>Payment Reminder</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>This is a friendly reminder that your invoice <strong>#{{invoice_number}}</strong> for <strong>${{amount_due}}</strong> is due on <strong>{{due_date}}</strong>.</p>
                    <p>Invoice Details:</p>
                    <ul>
                        <li>Invoice Number: {{invoice_number}}</li>
                        <li>Amount Due: ${{amount_due}}</li>
                        <li>Due Date: {{due_date}}</li>
                    </ul>
                    <p>To make a payment, please visit our customer portal or contact us at {{company_phone}}.</p>
                    <p>Thank you,<br>{{company_name}}</p>',
                'sms_body' => 'Payment reminder: Invoice #{{invoice_number}} for ${{amount_due}} is due on {{due_date}}. Please pay promptly to avoid service interruption. - {{company_name}}',
                'variables' => ['customer_name', 'invoice_number', 'amount_due', 'due_date', 'company_phone', 'company_name'],
                'is_active' => true,
            ],

            // Driver Dispatch
            [
                'name' => 'Driver Dispatch Notification',
                'slug' => 'driver-dispatch',
                'category' => NotificationCategory::DISPATCH,
                'available_types' => [NotificationType::SMS, NotificationType::PUSH],
                'subject' => 'New Route Assignment',
                'sms_body' => 'New route assigned: {{route_name}}. Starts at {{start_time}}. {{stop_count}} stops. Check app for details.',
                'push_body' => 'You have a new route assignment: {{route_name}}',
                'variables' => ['driver_name', 'route_name', 'start_time', 'stop_count', 'estimated_duration'],
                'is_active' => true,
            ],

            // Emergency Alerts
            [
                'name' => 'Emergency Service Alert',
                'slug' => 'emergency-alert',
                'category' => NotificationCategory::EMERGENCY,
                'available_types' => [NotificationType::EMAIL, NotificationType::SMS, NotificationType::PUSH],
                'subject' => 'URGENT: Service Disruption Alert',
                'email_body' => '<h2 style="color: red;">Emergency Service Alert</h2>
                    <p>Dear {{customer_name}},</p>
                    <p><strong>{{alert_title}}</strong></p>
                    <p>{{alert_message}}</p>
                    <p>Affected Services:</p>
                    <ul>
                        <li>Service Type: {{affected_service}}</li>
                        <li>Expected Resolution: {{resolution_time}}</li>
                    </ul>
                    <p>We apologize for any inconvenience. For updates, please contact us at {{company_phone}}.</p>
                    <p>{{company_name}} Emergency Response Team</p>',
                'sms_body' => 'URGENT: {{alert_title}}. {{alert_message}}. Expected resolution: {{resolution_time}}. Call {{company_phone}} for info.',
                'push_body' => 'Emergency: {{alert_title}}',
                'variables' => ['customer_name', 'alert_title', 'alert_message', 'affected_service', 'resolution_time', 'company_phone', 'company_name'],
                'is_active' => true,
            ],

            // Quote Confirmation
            [
                'name' => 'Quote Confirmation',
                'slug' => 'quote-confirmation',
                'category' => NotificationCategory::QUOTE,
                'available_types' => [NotificationType::EMAIL],
                'subject' => 'Your Quote #{{quote_number}} from {{company_name}}',
                'email_body' => '<h2>Quote Confirmation</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>Thank you for requesting a quote from {{company_name}}. Please find your quote details below:</p>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td><strong>Quote Number:</strong></td>
                            <td>{{quote_number}}</td>
                        </tr>
                        <tr>
                            <td><strong>Service Type:</strong></td>
                            <td>{{service_type}}</td>
                        </tr>
                        <tr>
                            <td><strong>Quoted Amount:</strong></td>
                            <td>${{quote_amount}}</td>
                        </tr>
                        <tr>
                            <td><strong>Valid Until:</strong></td>
                            <td>{{valid_until}}</td>
                        </tr>
                    </table>
                    <p>{{quote_details}}</p>
                    <p>To accept this quote, please contact us at {{company_phone}} or reply to this email.</p>
                    <p>Best regards,<br>{{company_name}}</p>',
                'variables' => ['customer_name', 'quote_number', 'service_type', 'quote_amount', 'valid_until', 'quote_details', 'company_phone', 'company_name'],
                'is_active' => true,
            ],

            // Service Completion
            [
                'name' => 'Service Completion Confirmation',
                'slug' => 'service-completion',
                'category' => NotificationCategory::SERVICE_REMINDER,
                'available_types' => [NotificationType::EMAIL, NotificationType::SMS],
                'subject' => 'Service Completed - {{service_type}}',
                'email_body' => '<h2>Service Completion Confirmation</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>We are pleased to confirm that your {{service_type}} service has been completed successfully.</p>
                    <p>Service Summary:</p>
                    <ul>
                        <li>Service Date: {{service_date}}</li>
                        <li>Completion Time: {{completion_time}}</li>
                        <li>Driver: {{driver_name}}</li>
                        <li>Notes: {{service_notes}}</li>
                    </ul>
                    <p>Thank you for choosing {{company_name}}. If you have any questions or concerns, please contact us at {{company_phone}}.</p>',
                'sms_body' => 'Service completed! Your {{service_type}} was completed at {{completion_time}}. Thank you for choosing {{company_name}}.',
                'variables' => ['customer_name', 'service_type', 'service_date', 'completion_time', 'driver_name', 'service_notes', 'company_phone', 'company_name'],
                'is_active' => true,
            ],

            // Welcome Email
            [
                'name' => 'Welcome New Customer',
                'slug' => 'welcome-customer',
                'category' => NotificationCategory::MARKETING,
                'available_types' => [NotificationType::EMAIL],
                'subject' => 'Welcome to {{company_name}}!',
                'email_body' => '<h2>Welcome to {{company_name}}!</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>Thank you for choosing {{company_name}} for your waste management needs. We are committed to providing you with reliable and efficient service.</p>
                    <p>Your Account Details:</p>
                    <ul>
                        <li>Account Number: {{account_number}}</li>
                        <li>Service Address: {{service_address}}</li>
                        <li>Service Start Date: {{start_date}}</li>
                        <li>Service Type: {{service_type}}</li>
                    </ul>
                    <p>What\'s Next?</p>
                    <ol>
                        <li>Your first service will be on {{first_service_date}}</li>
                        <li>Place your bins at the collection point before {{collection_time}}</li>
                        <li>Access your account online to manage services and view invoices</li>
                    </ol>
                    <p>If you have any questions, please don\'t hesitate to contact us at {{company_phone}} or {{company_email}}.</p>
                    <p>Welcome aboard!<br>The {{company_name}} Team</p>',
                'variables' => ['customer_name', 'company_name', 'account_number', 'service_address', 'start_date', 'service_type', 'first_service_date', 'collection_time', 'company_phone', 'company_email'],
                'is_active' => true,
            ],

            // Invoice Created
            [
                'name' => 'New Invoice',
                'slug' => 'invoice-created',
                'category' => NotificationCategory::INVOICE,
                'available_types' => [NotificationType::EMAIL],
                'subject' => 'New Invoice #{{invoice_number}} - {{company_name}}',
                'email_body' => '<h2>New Invoice</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>A new invoice has been generated for your account.</p>
                    <table style="width: 100%; border: 1px solid #ddd; padding: 10px;">
                        <tr>
                            <td><strong>Invoice Number:</strong></td>
                            <td>{{invoice_number}}</td>
                        </tr>
                        <tr>
                            <td><strong>Invoice Date:</strong></td>
                            <td>{{invoice_date}}</td>
                        </tr>
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td>{{due_date}}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Amount:</strong></td>
                            <td>${{total_amount}}</td>
                        </tr>
                    </table>
                    <p>{{invoice_details}}</p>
                    <p>Payment Options:</p>
                    <ul>
                        <li>Online: Visit our customer portal</li>
                        <li>Phone: Call {{company_phone}}</li>
                        <li>Mail: Send check to {{company_address}}</li>
                    </ul>
                    <p>Thank you for your business!</p>',
                'variables' => ['customer_name', 'invoice_number', 'invoice_date', 'due_date', 'total_amount', 'invoice_details', 'company_phone', 'company_address', 'company_name'],
                'is_active' => true,
            ],

            // Route Change Notification
            [
                'name' => 'Route Change Notification',
                'slug' => 'route-change',
                'category' => NotificationCategory::SERVICE_REMINDER,
                'available_types' => [NotificationType::EMAIL, NotificationType::SMS],
                'subject' => 'Important: Change to Your Collection Schedule',
                'email_body' => '<h2>Collection Schedule Change</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>We are writing to inform you of an important change to your waste collection schedule.</p>
                    <p><strong>Previous Schedule:</strong> {{old_schedule}}</p>
                    <p><strong>New Schedule:</strong> {{new_schedule}}</p>
                    <p><strong>Effective Date:</strong> {{effective_date}}</p>
                    <p>{{change_reason}}</p>
                    <p>Please adjust your bin placement accordingly. We apologize for any inconvenience this may cause.</p>
                    <p>If you have questions, please contact us at {{company_phone}}.</p>',
                'sms_body' => 'Schedule change: Your collection day is now {{new_schedule}} starting {{effective_date}}. Was {{old_schedule}}. Questions? Call {{company_phone}}',
                'variables' => ['customer_name', 'old_schedule', 'new_schedule', 'effective_date', 'change_reason', 'company_phone'],
                'is_active' => true,
            ],

            // Payment Received
            [
                'name' => 'Payment Received Confirmation',
                'slug' => 'payment-received',
                'category' => NotificationCategory::INVOICE,
                'available_types' => [NotificationType::EMAIL],
                'subject' => 'Payment Received - Invoice #{{invoice_number}}',
                'email_body' => '<h2>Payment Received</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>Thank you for your payment! We have successfully received your payment for invoice <strong>#{{invoice_number}}</strong>.</p>
                    <table style="width: 100%; border: 1px solid #ddd; padding: 10px;">
                        <tr>
                            <td><strong>Invoice Number:</strong></td>
                            <td>{{invoice_number}}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Amount:</strong></td>
                            <td>${{payment_amount}}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Date:</strong></td>
                            <td>{{payment_date}}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td>{{payment_method}}</td>
                        </tr>
                    </table>
                    <p>Your account has been updated and your balance is now current.</p>
                    <p>Thank you for your prompt payment and for choosing {{company_name}}!</p>',
                'variables' => ['customer_name', 'invoice_number', 'payment_amount', 'payment_date', 'payment_method', 'company_name'],
                'is_active' => true,
            ],

            // Overdue Payment
            [
                'name' => 'Overdue Payment Notice',
                'slug' => 'payment-overdue',
                'category' => NotificationCategory::PAYMENT_DUE,
                'available_types' => [NotificationType::EMAIL, NotificationType::SMS],
                'subject' => 'Overdue Notice - Invoice #{{invoice_number}}',
                'email_body' => '<h2 style="color: #d9534f;">Overdue Payment Notice</h2>
                    <p>Dear {{customer_name}},</p>
                    <p>Our records indicate that payment for invoice <strong>#{{invoice_number}}</strong> is now <strong>{{days_overdue}} days overdue</strong>.</p>
                    <table style="background: #f9f9f9; padding: 10px; width: 100%;">
                        <tr>
                            <td><strong>Invoice Number:</strong></td>
                            <td>{{invoice_number}}</td>
                        </tr>
                        <tr>
                            <td><strong>Original Due Date:</strong></td>
                            <td>{{due_date}}</td>
                        </tr>
                        <tr>
                            <td><strong>Amount Due:</strong></td>
                            <td>${{amount_due}}</td>
                        </tr>
                        <tr>
                            <td><strong>Late Fee:</strong></td>
                            <td>${{late_fee}}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Due:</strong></td>
                            <td><strong>${{total_due}}</strong></td>
                        </tr>
                    </table>
                    <p>To avoid service interruption, please make payment immediately by calling {{company_phone}} or visiting our customer portal.</p>
                    <p>If you have already made this payment, please disregard this notice.</p>',
                'sms_body' => 'OVERDUE: Invoice #{{invoice_number}} is {{days_overdue}} days past due. Amount: ${{total_due}}. Pay now to avoid service interruption: {{company_phone}}',
                'variables' => ['customer_name', 'invoice_number', 'days_overdue', 'due_date', 'amount_due', 'late_fee', 'total_due', 'company_phone'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            // Map template fields to database columns
            $dbTemplate = [
                'name' => $template['name'],
                'slug' => $template['slug'],
                'type' => NotificationType::EMAIL, // Default to email
                'category' => $template['category'],
                'subject_template' => $template['subject'] ?? '',
                'body_template' => $template['email_body'] ?? $template['sms_body'] ?? '',
                'available_variables' => json_encode($template['variables']),
                'is_active' => $template['is_active'],
                'is_system' => true,
                'company_id' => null, // System templates are available to all companies
            ];

            NotificationTemplate::updateOrCreate(
                ['slug' => $dbTemplate['slug']],
                $dbTemplate
            );

            // Create SMS version if available
            if (isset($template['sms_body']) && in_array(NotificationType::SMS, $template['available_types'])) {
                $smsTemplate = $dbTemplate;
                $smsTemplate['slug'] = $template['slug'].'-sms';
                $smsTemplate['name'] = $template['name'].' (SMS)';
                $smsTemplate['type'] = NotificationType::SMS;
                $smsTemplate['body_template'] = $template['sms_body'];
                $smsTemplate['subject_template'] = null;

                NotificationTemplate::updateOrCreate(
                    ['slug' => $smsTemplate['slug']],
                    $smsTemplate
                );
            }
        }
    }
}
