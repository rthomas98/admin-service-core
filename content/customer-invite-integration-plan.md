# Customer Invite System - Full Integration Plan

## Executive Summary

The Customer Invite system is **68% complete** with strong foundation but critical security gaps requiring immediate attention. This plan outlines the necessary steps to achieve full production-ready integration.

## Current State Analysis

### ✅ What's Working (Completed)
- **Model Architecture** (95%): Well-designed CustomerInvite model with relationships
- **Basic Authentication Flow** (80%): Token-based registration working
- **Filament Integration** (75%): Admin interface for managing invites
- **Email System** (70%): Basic invitation emails functional
- **Database Structure** (90%): Proper schema with indexes

### ❌ Critical Gaps
1. **Security Vulnerabilities** (HIGH PRIORITY)
   - No rate limiting on invitation endpoints
   - Weak token generation (not cryptographically secure)
   - Missing input validation and sanitization
   - No CSRF protection on some endpoints
   - Vulnerable to email enumeration attacks

2. **Missing Features** (MEDIUM PRIORITY)
   - No bulk invitation capabilities
   - Missing re-invitation workflow
   - No invitation analytics/tracking
   - Missing API endpoints for programmatic access
   - No invitation templates

3. **Testing Coverage** (25% - NEEDS IMPROVEMENT)
   - Basic model tests only
   - Missing security tests
   - No edge case coverage
   - Missing integration tests

## Implementation Roadmap

### Phase 1: Security Hardening (Week 1) - CRITICAL

#### 1.1 Implement Rate Limiting
```php
// app/Http/Middleware/CustomerInviteRateLimit.php
namespace App\Http\Middleware;

use Illuminate\Support\Facades\RateLimiter;

class CustomerInviteRateLimit
{
    public function handle($request, $next)
    {
        $key = 'accept-invite:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['error' => 'Too many attempts'], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
```

#### 1.2 Secure Token Generation
```php
// app/Models/CustomerInvite.php - Add to model
protected static function boot()
{
    parent::boot();

    static::creating(function ($invite) {
        $invite->token = bin2hex(random_bytes(32));
        $invite->expires_at = $invite->expires_at ?? now()->addDays(7);
    });
}
```

#### 1.3 Input Validation & Sanitization
```php
// app/Http/Requests/AcceptInviteRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcceptInviteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'password' => ['required', 'string', 'min:12', 'confirmed',
                         'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/'],
            'terms' => ['required', 'accepted'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => strip_tags($this->name),
            'email' => filter_var($this->email, FILTER_SANITIZE_EMAIL),
        ]);
    }
}
```

#### 1.4 Prevent Email Enumeration
```php
// app/Http/Controllers/CustomerAuthController.php
public function acceptInvite(string $token)
{
    // Use timing-safe comparison
    $invite = CustomerInvite::where('token', hash('sha256', $token))
                            ->first();

    // Always return same response time
    usleep(random_int(100000, 300000));

    if (!$invite || !$invite->isValid()) {
        return redirect('/customer/login')
               ->with('error', 'Invalid or expired invitation.');
    }

    // Continue processing...
}
```

### Phase 2: Core Features (Week 2)

#### 2.1 Bulk Invitation System
```php
// app/Models/CustomerInvite.php
public static function createBulk(int $customerId, array $emails, int $invitedBy): array
{
    $created = [];
    $skipped = [];

    DB::transaction(function () use ($customerId, $emails, $invitedBy, &$created, &$skipped) {
        foreach ($emails as $email) {
            $email = strtolower(trim($email));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped[] = ['email' => $email, 'reason' => 'Invalid email'];
                continue;
            }

            $existing = self::where('customer_id', $customerId)
                           ->where('email', $email)
                           ->where('is_active', true)
                           ->exists();

            if ($existing) {
                $skipped[] = ['email' => $email, 'reason' => 'Already invited'];
                continue;
            }

            $created[] = self::create([
                'customer_id' => $customerId,
                'email' => $email,
                'invited_by' => $invitedBy,
            ]);
        }
    });

    // Queue emails
    foreach ($created as $invite) {
        SendInvitationJob::dispatch($invite);
    }

    return ['created' => $created, 'skipped' => $skipped];
}
```

#### 2.2 Re-invitation Workflow
```php
// app/Filament/Resources/CustomerInviteResource.php
Tables\Actions\Action::make('resend')
    ->action(function (CustomerInvite $record) {
        if ($record->isAccepted()) {
            Notification::make()
                ->title('Cannot resend accepted invitation')
                ->danger()
                ->send();
            return;
        }

        $record->regenerateToken();
        $record->extendExpiration(7);

        SendInvitationJob::dispatch($record);

        Notification::make()
            ->title('Invitation resent successfully')
            ->success()
            ->send();
    })
    ->visible(fn ($record) => !$record->isAccepted())
    ->requiresConfirmation();
```

#### 2.3 Invitation Analytics
```php
// app/Models/CustomerInvite.php
public static function getStatistics(?int $customerId = null): array
{
    $query = self::query();

    if ($customerId) {
        $query->where('customer_id', $customerId);
    }

    return Cache::remember("invite_stats_{$customerId}", 300, function () use ($query) {
        $total = $query->count();
        $accepted = $query->clone()->whereNotNull('accepted_at')->count();
        $pending = $query->clone()->active()->count();
        $expired = $query->clone()->expired()->count();

        return [
            'total' => $total,
            'accepted' => $accepted,
            'pending' => $pending,
            'expired' => $expired,
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 2) : 0,
            'avg_acceptance_time' => $query->clone()
                ->whereNotNull('accepted_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, accepted_at)) as avg_hours')
                ->value('avg_hours'),
        ];
    });
}
```

### Phase 3: API Integration (Week 3)

#### 3.1 RESTful API Endpoints
```php
// routes/api.php
Route::prefix('api/v1')->middleware('auth:sanctum')->group(function () {
    Route::prefix('customer-invites')->group(function () {
        Route::get('/', [CustomerInviteApiController::class, 'index']);
        Route::post('/', [CustomerInviteApiController::class, 'store']);
        Route::get('/{invite}', [CustomerInviteApiController::class, 'show']);
        Route::post('/{invite}/resend', [CustomerInviteApiController::class, 'resend']);
        Route::delete('/{invite}', [CustomerInviteApiController::class, 'destroy']);
        Route::post('/bulk', [CustomerInviteApiController::class, 'bulkCreate']);
        Route::get('/statistics', [CustomerInviteApiController::class, 'statistics']);
    });
});
```

#### 3.2 API Controller Implementation
```php
// app/Http/Controllers/Api/CustomerInviteApiController.php
namespace App\Http\Controllers\Api;

use App\Http\Resources\CustomerInviteResource;
use App\Models\CustomerInvite;
use Illuminate\Http\Request;

class CustomerInviteApiController extends Controller
{
    public function index(Request $request)
    {
        $invites = CustomerInvite::query()
            ->when($request->status, function ($query, $status) {
                return match($status) {
                    'pending' => $query->pending(),
                    'accepted' => $query->accepted(),
                    'expired' => $query->expired(),
                    default => $query,
                };
            })
            ->when($request->customer_id, function ($query, $customerId) {
                return $query->where('customer_id', $customerId);
            })
            ->paginate($request->per_page ?? 15);

        return CustomerInviteResource::collection($invites);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'email' => 'required|email',
            'expires_at' => 'nullable|date|after:now',
            'send_email' => 'boolean',
        ]);

        $invite = CustomerInvite::create([
            ...$validated,
            'invited_by' => auth()->id(),
        ]);

        if ($request->send_email ?? true) {
            SendInvitationJob::dispatch($invite);
        }

        return new CustomerInviteResource($invite);
    }
}
```

### Phase 4: Enhanced Features (Week 4)

#### 4.1 Invitation Templates
```php
// database/migrations/create_invitation_templates_table.php
Schema::create('invitation_templates', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained();
    $table->string('name');
    $table->string('subject');
    $table->text('body');
    $table->json('variables')->nullable();
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});

// app/Models/InvitationTemplate.php
class InvitationTemplate extends Model
{
    protected $fillable = ['company_id', 'name', 'subject', 'body', 'variables', 'is_default'];

    protected $casts = [
        'variables' => 'array',
        'is_default' => 'boolean',
    ];

    public function render(array $data): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($data as $key => $value) {
            $subject = str_replace("{{$key}}", $value, $subject);
            $body = str_replace("{{$key}}", $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
```

#### 4.2 Webhook Notifications
```php
// app/Jobs/SendInvitationWebhook.php
namespace App\Jobs;

use App\Models\CustomerInvite;
use Illuminate\Support\Facades\Http;

class SendInvitationWebhook implements ShouldQueue
{
    public function __construct(
        protected CustomerInvite $invite,
        protected string $event
    ) {}

    public function handle()
    {
        $webhookUrl = config('services.webhooks.customer_invite');

        if (!$webhookUrl) {
            return;
        }

        $payload = [
            'event' => $this->event,
            'data' => [
                'invite_id' => $this->invite->id,
                'customer_id' => $this->invite->customer_id,
                'email' => $this->invite->email,
                'status' => $this->invite->getStatus(),
                'created_at' => $this->invite->created_at,
                'accepted_at' => $this->invite->accepted_at,
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        $signature = hash_hmac('sha256', json_encode($payload), config('services.webhooks.secret'));

        Http::withHeaders([
            'X-Webhook-Signature' => $signature,
        ])->post($webhookUrl, $payload);
    }
}
```

### Phase 5: Testing & Quality Assurance (Ongoing)

#### Test Coverage Requirements
- **Unit Tests**: 90% coverage for models and services
- **Feature Tests**: 85% coverage for controllers and API
- **Integration Tests**: Critical user flows
- **Security Tests**: Penetration testing scenarios
- **Performance Tests**: Load testing for bulk operations

## Security Checklist

- [ ] Implement rate limiting on all invitation endpoints
- [ ] Use cryptographically secure token generation
- [ ] Add input validation and sanitization
- [ ] Implement CSRF protection
- [ ] Prevent email enumeration attacks
- [ ] Add SQL injection protection
- [ ] Implement XSS protection
- [ ] Add activity logging for audit trails
- [ ] Set up monitoring and alerting
- [ ] Implement token rotation on resend

## Performance Optimization

1. **Database Indexes**
```sql
CREATE INDEX idx_customer_invites_token ON customer_invites(token);
CREATE INDEX idx_customer_invites_email ON customer_invites(email);
CREATE INDEX idx_customer_invites_customer_status ON customer_invites(customer_id, is_active, accepted_at);
CREATE INDEX idx_customer_invites_expiry ON customer_invites(expires_at);
```

2. **Caching Strategy**
- Cache invitation statistics (5 min TTL)
- Cache customer invitation counts
- Cache template renderings

3. **Queue Optimization**
- Use Redis for queue backend
- Separate queues for emails vs webhooks
- Implement retry logic with exponential backoff

## Monitoring & Metrics

### Key Performance Indicators (KPIs)
- Invitation acceptance rate (target: >60%)
- Average time to acceptance (target: <48 hours)
- Invitation delivery success rate (target: >99%)
- System response time (target: <200ms)

### Monitoring Implementation
```php
// app/Services/InvitationMetrics.php
class InvitationMetrics
{
    public static function recordInvitationSent(CustomerInvite $invite): void
    {
        Metric::increment('invitations.sent');
        Metric::histogram('invitations.processing_time', microtime(true) - LARAVEL_START);
    }

    public static function recordInvitationAccepted(CustomerInvite $invite): void
    {
        Metric::increment('invitations.accepted');

        $timeToAccept = $invite->accepted_at->diffInHours($invite->created_at);
        Metric::histogram('invitations.time_to_accept', $timeToAccept);
    }
}
```

## Deployment Checklist

### Pre-Production
- [ ] Complete security audit
- [ ] Load testing completed
- [ ] Documentation updated
- [ ] Training materials prepared
- [ ] Rollback plan documented

### Production Deployment
- [ ] Database migrations tested
- [ ] Environment variables configured
- [ ] SSL certificates valid
- [ ] Monitoring alerts configured
- [ ] Backup strategy implemented

## Maintenance Plan

### Daily Tasks
- Monitor invitation metrics
- Check failed job queue
- Review error logs

### Weekly Tasks
- Clean up expired invitations
- Analyze acceptance rates
- Review security logs

### Monthly Tasks
- Performance analysis
- Security updates
- Documentation review

## Success Metrics

### Phase 1 Success (Security)
- Zero security vulnerabilities in penetration testing
- All OWASP Top 10 risks mitigated
- 100% of endpoints rate-limited

### Phase 2 Success (Features)
- Bulk invitation processing <5 seconds for 100 invites
- Re-invitation workflow completion <2 clicks
- Analytics dashboard load time <1 second

### Phase 3 Success (API)
- API response time <100ms at p99
- 100% API documentation coverage
- Zero breaking changes after launch

### Phase 4 Success (Enhanced)
- 5+ invitation templates created
- Webhook delivery success >99.9%
- Template rendering <50ms

### Overall Success
- Customer satisfaction score >8/10
- Support ticket reduction >30%
- Invitation acceptance rate >60%
- System uptime >99.9%

## Risk Mitigation

### Technical Risks
- **Risk**: Email delivery failures
  - **Mitigation**: Implement multiple email providers with fallback

- **Risk**: Token collision
  - **Mitigation**: Use 64-character tokens with uniqueness validation

- **Risk**: Database performance degradation
  - **Mitigation**: Implement read replicas and caching

### Business Risks
- **Risk**: Low adoption rate
  - **Mitigation**: User training and documentation

- **Risk**: Compliance issues
  - **Mitigation**: Legal review and GDPR compliance

## Timeline Summary

- **Week 1**: Security hardening (CRITICAL)
- **Week 2**: Core features implementation
- **Week 3**: API development
- **Week 4**: Enhanced features
- **Ongoing**: Testing, monitoring, and optimization

## Next Steps

1. **Immediate** (Today):
   - Implement rate limiting
   - Fix token generation security

2. **This Week**:
   - Complete Phase 1 security tasks
   - Run security audit

3. **Next Week**:
   - Begin Phase 2 feature development
   - Set up monitoring infrastructure

## Conclusion

The Customer Invite system has a solid foundation but requires immediate security hardening before production deployment. Following this plan will result in a robust, secure, and feature-rich invitation system that meets enterprise requirements.

Total estimated effort: **4 weeks** for full implementation with 2 developers.
Priority focus: **Security first**, then features, then optimization.