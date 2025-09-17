<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerInviteTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'subject',
        'body',
        'variables',
        'settings',
        'is_active',
        'is_default',
        'expiration_days',
        'language',
        'usage_count',
        'last_used_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        // Auto-generate slug from name
        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name.'-'.$template->language);
            }

            // Set creator
            if (empty($template->created_by)) {
                $template->created_by = Auth::id();
            }

            // Ensure only one default template per company/language
            if ($template->is_default) {
                static::where('company_id', $template->company_id)
                    ->where('language', $template->language)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });

        static::updating(function ($template) {
            // Set updater
            $template->updated_by = Auth::id();

            // Ensure only one default template per company/language
            if ($template->is_default && $template->isDirty('is_default')) {
                static::where('company_id', $template->company_id)
                    ->where('language', $template->language)
                    ->where('is_default', true)
                    ->where('id', '!=', $template->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    // Helper Methods
    public function render(array $data = []): array
    {
        $subject = $this->subject;
        $body = $this->body;

        // Replace variables in subject and body
        foreach ($data as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', $value, $subject);
            $body = str_replace('{{'.$key.'}}', $value, $body);
        }

        // Replace any remaining variables with defaults or empty string
        $subject = preg_replace('/\{\{.*?\}\}/', '', $subject);
        $body = preg_replace('/\{\{.*?\}\}/', '', $body);

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function duplicate(?string $name = null): self
    {
        $duplicate = $this->replicate(['slug', 'is_default', 'usage_count', 'last_used_at']);
        $duplicate->name = $name ?? $this->name.' (Copy)';
        $duplicate->slug = Str::slug($duplicate->name.'-'.$duplicate->language);
        $duplicate->is_default = false;
        $duplicate->usage_count = 0;
        $duplicate->last_used_at = null;
        $duplicate->save();

        return $duplicate;
    }

    // Static Methods
    public static function getDefault(int $companyId, string $language = 'en'): ?self
    {
        return static::forCompany($companyId)
            ->forLanguage($language)
            ->default()
            ->active()
            ->first();
    }

    public static function getDefaultOrFirst(int $companyId, string $language = 'en'): ?self
    {
        $default = static::getDefault($companyId, $language);

        if (! $default) {
            $default = static::forCompany($companyId)
                ->forLanguage($language)
                ->active()
                ->first();
        }

        return $default;
    }

    // Default Templates
    public static function createDefaultTemplates(int $companyId): void
    {
        $templates = [
            [
                'name' => 'Standard Invitation',
                'slug' => 'standard-invitation-en',
                'description' => 'Default invitation template for new customer portal access',
                'subject' => 'You\'re invited to join {{company_name}}\'s Customer Portal',
                'body' => <<<'HTML'
<p>Hello {{customer_name}},</p>

<p>{{company_name}} has invited you to access their customer portal where you can:</p>

<ul>
    <li>View and pay invoices online</li>
    <li>Submit and track service requests</li>
    <li>Access important documents</li>
    <li>Manage your account information</li>
    <li>Receive notifications and updates</li>
</ul>

<p>To get started, please click the link below to create your account:</p>

<p><a href="{{registration_url}}" style="display: inline-block; padding: 12px 24px; background-color: #3B82F6; color: white; text-decoration: none; border-radius: 6px;">Create Your Account</a></p>

<p>This invitation will expire on {{expiration_date}}.</p>

<p>If you have any questions, please don't hesitate to contact us.</p>

<p>Best regards,<br>
The {{company_name}} Team</p>
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
                    'button_color' => '#3B82F6',
                ],
                'is_active' => true,
                'is_default' => true,
                'expiration_days' => 7,
                'language' => 'en',
            ],
            [
                'name' => 'Welcome Back',
                'slug' => 'welcome-back-en',
                'description' => 'Template for existing customers who need portal access',
                'subject' => 'Welcome back! Access your {{company_name}} Customer Portal',
                'body' => <<<'HTML'
<p>Dear {{customer_name}},</p>

<p>We're excited to give you access to our new customer portal!</p>

<p>As a valued customer of {{company_name}}, you can now manage your account online 24/7.</p>

<p><strong>What's available in your portal:</strong></p>
<ul>
    <li>View your complete service history</li>
    <li>Access and download invoices</li>
    <li>Submit new service requests</li>
    <li>Track request status in real-time</li>
    <li>Update your contact information</li>
</ul>

<p><a href="{{registration_url}}" style="display: inline-block; padding: 12px 24px; background-color: #10B981; color: white; text-decoration: none; border-radius: 6px;">Activate Your Account</a></p>

<p><small>This link expires on {{expiration_date}}</small></p>

<p>Questions? Contact us at {{support_email}} or call {{support_phone}}</p>

<p>Thank you for your continued business!</p>

<p>Sincerely,<br>
{{company_name}}</p>
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
                    'button_color' => '#10B981',
                ],
                'is_active' => true,
                'is_default' => false,
                'expiration_days' => 14,
                'language' => 'en',
            ],
            [
                'name' => 'Urgent Access Required',
                'slug' => 'urgent-access-en',
                'description' => 'Template for time-sensitive portal access requirements',
                'subject' => 'Action Required: Set up your {{company_name}} portal access',
                'body' => <<<'HTML'
<p>{{customer_name}},</p>

<p><strong>Important: You need to set up your customer portal access to continue receiving our services.</strong></p>

<p>{{company_name}} requires all customers to have active portal accounts for:</p>
<ul>
    <li>Invoice delivery and payment</li>
    <li>Service scheduling</li>
    <li>Important notifications</li>
</ul>

<p>Please set up your account immediately:</p>

<p><a href="{{registration_url}}" style="display: inline-block; padding: 14px 28px; background-color: #EF4444; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">Set Up Account Now</a></p>

<p><strong>⚠️ This invitation expires on {{expiration_date}}</strong></p>

<p>If you don't set up your account by the expiration date, you may experience service interruptions.</p>

<p>Need help? Contact us immediately at {{support_phone}}</p>

<p>{{company_name}}</p>
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
                    'button_color' => '#EF4444',
                    'is_urgent' => true,
                ],
                'is_active' => true,
                'is_default' => false,
                'expiration_days' => 3,
                'language' => 'en',
            ],
        ];

        foreach ($templates as $templateData) {
            $templateData['company_id'] = $companyId;
            static::create($templateData);
        }
    }
}
