<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'type',
        'category',
        'subject_template',
        'body_template',
        'available_variables',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'category' => NotificationCategory::class,
        'available_variables' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where(function ($query) {
                    $query->where('notification_templates.company_id', auth()->user()->company_id)
                          ->orWhereNull('notification_templates.company_id');
                });
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForType(Builder $query, NotificationType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForCategory(Builder $query, NotificationCategory $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    public function scopeCustom(Builder $query): Builder
    {
        return $query->where('is_system', false);
    }

    // Methods
    public function render(array $data = []): array
    {
        $subject = $this->renderTemplate($this->subject_template, $data);
        $body = $this->renderTemplate($this->body_template, $data);
        
        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    protected function renderTemplate(?string $template, array $data): ?string
    {
        if (!$template) {
            return null;
        }

        foreach ($data as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $template = str_replace(
                    ['{{' . $key . '}}', '{{ ' . $key . ' }}'],
                    $value ?? '',
                    $template
                );
            }
        }

        return $template;
    }

    public function getAvailableVariablesFormatted(): string
    {
        if (empty($this->available_variables)) {
            return '';
        }

        return implode(', ', array_map(fn($var) => '{{' . $var . '}}', $this->available_variables));
    }
}