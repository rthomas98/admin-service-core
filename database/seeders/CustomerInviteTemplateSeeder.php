<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CustomerInviteTemplate;
use Illuminate\Database\Seeder;

class CustomerInviteTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get RAW Disposal company
        $rawDisposal = Company::where('name', 'RAW Disposal')->first();

        if ($rawDisposal) {
            $this->command->info('Creating invitation templates for RAW Disposal...');
            CustomerInviteTemplate::createDefaultTemplates($rawDisposal->id);
            $this->command->info('✓ Created 3 default invitation templates for RAW Disposal');
        } else {
            $this->command->warn('RAW Disposal company not found. Skipping template creation.');
        }

        // Get LIV Transport company
        $livTransport = Company::where('name', 'LIV Transport')->first();

        if ($livTransport) {
            $this->command->info('Creating invitation templates for LIV Transport...');

            // Create LIV Transport specific templates
            $templates = [
                [
                    'company_id' => $livTransport->id,
                    'name' => 'Fleet Customer Portal',
                    'slug' => 'fleet-customer-portal-en',
                    'description' => 'Invitation for fleet management customers',
                    'subject' => 'Access your LIV Transport Fleet Portal',
                    'body' => <<<'HTML'
<p>Hello {{customer_name}},</p>

<p>Welcome to LIV Transport's Fleet Management Portal!</p>

<p>As our valued transportation partner, you now have 24/7 access to:</p>

<ul>
    <li>📊 Real-time fleet tracking and status</li>
    <li>📋 Service history and maintenance records</li>
    <li>💰 Invoice management and payment</li>
    <li>🚚 Schedule pickups and deliveries</li>
    <li>📱 Mobile app access for drivers</li>
</ul>

<p>Get started with your account:</p>

<p><a href="{{registration_url}}" style="display: inline-block; padding: 12px 24px; background-color: #059669; color: white; text-decoration: none; border-radius: 6px;">Activate Fleet Portal Access</a></p>

<p>This invitation expires on {{expiration_date}}.</p>

<p>Need assistance? Our fleet support team is ready to help:<br>
📧 {{support_email}}<br>
📞 {{support_phone}}</p>

<p>Safe travels,<br>
The LIV Transport Team</p>
HTML,
                    'variables' => [
                        'customer_name',
                        'company_name',
                        'registration_url',
                        'expiration_date',
                        'support_email',
                        'support_phone',
                    ],
                    'settings' => [
                        'show_company_logo' => true,
                        'show_support_info' => true,
                        'button_color' => '#059669',
                        'industry' => 'transport',
                    ],
                    'is_active' => true,
                    'is_default' => true,
                    'expiration_days' => 7,
                    'language' => 'en',
                ],
            ];

            foreach ($templates as $templateData) {
                CustomerInviteTemplate::create($templateData);
            }

            $this->command->info('✓ Created custom invitation template for LIV Transport');
        } else {
            $this->command->warn('LIV Transport company not found. Skipping template creation.');
        }

        $this->command->info('Invitation template seeding completed!');
    }
}
