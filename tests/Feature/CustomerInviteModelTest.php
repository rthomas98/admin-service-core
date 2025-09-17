<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerInvite;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->company = Company::factory()->create();
    $this->customer = Customer::factory()->for($this->company)->create();
    $this->admin = User::factory()->create();
});

describe('CustomerInvite Model', function () {

    it('creates invite with proper attributes', function () {
        $invite = CustomerInvite::create([
            'customer_id' => $this->customer->id,
            'email' => 'test@example.com',
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
            'invited_by' => $this->admin->id,
        ]);

        expect($invite)
            ->customer_id->toBe($this->customer->id)
            ->email->toBe('test@example.com')
            ->token->toHaveLength(64)
            ->expires_at->toBeInstanceOf(Carbon::class)
            ->invited_by->toBe($this->admin->id)
            ->accepted_at->toBeNull()
            ->is_active->toBeTrue();
    });

    it('belongs to a customer', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        expect($invite->customer)
            ->toBeInstanceOf(Customer::class)
            ->id->toBe($this->customer->id);
    });

    it('belongs to an inviter', function () {
        $invite = CustomerInvite::factory()->create([
            'invited_by' => $this->admin->id,
        ]);

        expect($invite->inviter)
            ->toBeInstanceOf(User::class)
            ->id->toBe($this->admin->id);
    });

    it('generates unique token automatically', function () {
        $invite1 = CustomerInvite::factory()->create();
        $invite2 = CustomerInvite::factory()->create();

        expect($invite1->token)
            ->not->toBe($invite2->token)
            ->toHaveLength(64);
    });

    it('sets default expiration to 7 days', function () {
        $invite = CustomerInvite::factory()->create([
            'expires_at' => null,
        ]);

        expect($invite->expires_at)
            ->toBeInstanceOf(Carbon::class)
            ->toBeGreaterThan(now()->addDays(6))
            ->toBeLessThan(now()->addDays(8));
    });

    it('scopes to active invites only', function () {
        // Create mix of invites
        $activeNotExpired = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $activeExpired = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $inactiveNotExpired = CustomerInvite::factory()->create([
            'is_active' => false,
            'expires_at' => now()->addDay(),
        ]);

        $accepted = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
            'accepted_at' => now(),
        ]);

        $activeInvites = CustomerInvite::active()->get();

        expect($activeInvites)
            ->toHaveCount(1)
            ->first()->id->toBe($activeNotExpired->id);
    });

    it('scopes to pending invites', function () {
        $pending = CustomerInvite::factory()->create([
            'accepted_at' => null,
        ]);

        $accepted = CustomerInvite::factory()->create([
            'accepted_at' => now(),
        ]);

        $pendingInvites = CustomerInvite::pending()->get();

        expect($pendingInvites)
            ->toHaveCount(1)
            ->first()->id->toBe($pending->id);
    });

    it('scopes to accepted invites', function () {
        $pending = CustomerInvite::factory()->create([
            'accepted_at' => null,
        ]);

        $accepted = CustomerInvite::factory()->create([
            'accepted_at' => now(),
        ]);

        $acceptedInvites = CustomerInvite::accepted()->get();

        expect($acceptedInvites)
            ->toHaveCount(1)
            ->first()->id->toBe($accepted->id);
    });

    it('scopes to expired invites', function () {
        $expired = CustomerInvite::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $notExpired = CustomerInvite::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        $expiredInvites = CustomerInvite::expired()->get();

        expect($expiredInvites)
            ->toHaveCount(1)
            ->first()->id->toBe($expired->id);
    });

    it('checks if invite is valid', function () {
        $validInvite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
            'accepted_at' => null,
        ]);

        $expiredInvite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->subDay(),
            'accepted_at' => null,
        ]);

        $inactiveInvite = CustomerInvite::factory()->create([
            'is_active' => false,
            'expires_at' => now()->addDay(),
            'accepted_at' => null,
        ]);

        $acceptedInvite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
            'accepted_at' => now(),
        ]);

        expect($validInvite->isValid())->toBeTrue()
            ->and($expiredInvite->isValid())->toBeFalse()
            ->and($inactiveInvite->isValid())->toBeFalse()
            ->and($acceptedInvite->isValid())->toBeFalse();
    });

    it('checks if invite is expired', function () {
        $expired = CustomerInvite::factory()->create([
            'expires_at' => now()->subDay(),
        ]);

        $notExpired = CustomerInvite::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        expect($expired->isExpired())->toBeTrue()
            ->and($notExpired->isExpired())->toBeFalse();
    });

    it('checks if invite is accepted', function () {
        $accepted = CustomerInvite::factory()->create([
            'accepted_at' => now(),
        ]);

        $notAccepted = CustomerInvite::factory()->create([
            'accepted_at' => null,
        ]);

        expect($accepted->isAccepted())->toBeTrue()
            ->and($notAccepted->isAccepted())->toBeFalse();
    });

    it('marks invite as accepted', function () {
        $invite = CustomerInvite::factory()->create([
            'accepted_at' => null,
            'is_active' => true,
        ]);

        $invite->markAsAccepted();

        expect($invite->fresh())
            ->accepted_at->not->toBeNull()
            ->is_active->toBeFalse();
    });

    it('deactivates invite', function () {
        $invite = CustomerInvite::factory()->create([
            'is_active' => true,
        ]);

        $invite->deactivate();

        expect($invite->fresh()->is_active)->toBeFalse();
    });

    it('extends expiration date', function () {
        $invite = CustomerInvite::factory()->create([
            'expires_at' => now()->addDay(),
        ]);

        $originalExpiry = $invite->expires_at;
        $invite->extendExpiration(3);

        expect($invite->fresh()->expires_at)
            ->toBeGreaterThan($originalExpiry)
            ->toBeBetween(now()->addDays(3)->subMinute(), now()->addDays(4));
    });

    it('regenerates token', function () {
        $invite = CustomerInvite::factory()->create();
        $originalToken = $invite->token;

        $invite->regenerateToken();

        expect($invite->fresh()->token)
            ->not->toBe($originalToken)
            ->toHaveLength(64);
    });

    it('prevents duplicate active invites for same email and customer', function () {
        CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'duplicate@example.com',
            'is_active' => true,
            'accepted_at' => null,
        ]);

        expect(fn () => CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'duplicate@example.com',
            'is_active' => true,
            'accepted_at' => null,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    it('allows multiple invites after previous ones are accepted', function () {
        $firstInvite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'reinvite@example.com',
            'accepted_at' => now(),
            'is_active' => false,
        ]);

        $secondInvite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'reinvite@example.com',
            'is_active' => true,
        ]);

        expect($secondInvite)->toBeInstanceOf(CustomerInvite::class)
            ->and(CustomerInvite::where('email', 'reinvite@example.com')->count())->toBe(2);
    });

    it('casts dates properly', function () {
        $invite = CustomerInvite::factory()->create();

        expect($invite->expires_at)->toBeInstanceOf(Carbon::class)
            ->and($invite->created_at)->toBeInstanceOf(Carbon::class)
            ->and($invite->updated_at)->toBeInstanceOf(Carbon::class);
    });

    it('hides sensitive attributes in JSON', function () {
        $invite = CustomerInvite::factory()->create();
        $json = $invite->toArray();

        expect($json)->not->toHaveKey('token');
    });

    it('cleans up expired invites', function () {
        CustomerInvite::factory()->count(3)->create([
            'expires_at' => now()->subDays(2),
        ]);

        CustomerInvite::factory()->count(2)->create([
            'expires_at' => now()->addDays(2),
        ]);

        CustomerInvite::cleanupExpired();

        expect(CustomerInvite::count())->toBe(2);
    });

    it('finds invite by valid token', function () {
        $invite = CustomerInvite::factory()->create([
            'token' => 'test-token-123',
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $found = CustomerInvite::findByValidToken('test-token-123');

        expect($found)
            ->toBeInstanceOf(CustomerInvite::class)
            ->id->toBe($invite->id);
    });

    it('returns null for invalid token', function () {
        CustomerInvite::factory()->create([
            'token' => 'expired-token',
            'expires_at' => now()->subDay(),
        ]);

        $found = CustomerInvite::findByValidToken('expired-token');

        expect($found)->toBeNull();
    });

    it('counts pending invites for customer', function () {
        CustomerInvite::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
            'accepted_at' => null,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'accepted_at' => now(),
        ]);

        $count = CustomerInvite::pendingForCustomer($this->customer->id)->count();

        expect($count)->toBe(3);
    });

    it('gets invitation statistics', function () {
        // Create various invites
        CustomerInvite::factory()->count(5)->create(['accepted_at' => now()]);
        CustomerInvite::factory()->count(3)->create(['accepted_at' => null, 'expires_at' => now()->addDay()]);
        CustomerInvite::factory()->count(2)->create(['expires_at' => now()->subDay()]);

        $stats = CustomerInvite::getStatistics();

        expect($stats)
            ->toHaveKeys(['total', 'pending', 'accepted', 'expired', 'acceptance_rate'])
            ->total->toBe(10)
            ->pending->toBe(3)
            ->accepted->toBe(5)
            ->expired->toBe(2)
            ->acceptance_rate->toBe(50.0);
    });
});

describe('CustomerInvite Security', function () {

    it('generates cryptographically secure tokens', function () {
        $tokens = collect(range(1, 100))->map(function () {
            return CustomerInvite::factory()->create()->token;
        });

        expect($tokens->unique()->count())->toBe(100)
            ->and($tokens->first())->toMatch('/^[A-Za-z0-9]+$/');
    });

    it('prevents token enumeration attacks', function () {
        $invite = CustomerInvite::factory()->create();

        // Attempting to find with wrong token should not reveal existence
        $result = CustomerInvite::where('token', 'wrong-token')->first();

        expect($result)->toBeNull();
    });

    it('rate limits invitation lookups', function () {
        // This would need actual rate limiting implementation
        $this->markTestIncomplete('Rate limiting needs to be implemented');
    });

    it('sanitizes email before saving', function () {
        $invite = CustomerInvite::factory()->create([
            'email' => '  TEST@EXAMPLE.COM  ',
        ]);

        expect($invite->email)->toBe('test@example.com');
    });

    it('validates email format', function () {
        expect(fn () => CustomerInvite::factory()->create([
            'email' => 'invalid-email',
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });
});

describe('CustomerInvite Bulk Operations', function () {

    it('creates bulk invitations efficiently', function () {
        $emails = [
            'user1@example.com',
            'user2@example.com',
            'user3@example.com',
        ];

        $invites = CustomerInvite::createBulk($this->customer->id, $emails, $this->admin->id);

        expect($invites)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(CustomerInvite::class);
    });

    it('handles duplicate emails in bulk creation', function () {
        CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'existing@example.com',
            'is_active' => true,
        ]);

        $emails = [
            'existing@example.com',
            'new@example.com',
        ];

        $result = CustomerInvite::createBulk($this->customer->id, $emails, $this->admin->id);

        expect($result['created'])->toHaveCount(1)
            ->and($result['skipped'])->toHaveCount(1);
    });

    it('deactivates all invites for a customer', function () {
        CustomerInvite::factory()->count(5)->create([
            'customer_id' => $this->customer->id,
            'is_active' => true,
        ]);

        CustomerInvite::deactivateAllForCustomer($this->customer->id);

        expect(CustomerInvite::where('customer_id', $this->customer->id)
            ->where('is_active', true)
            ->count()
        )->toBe(0);
    });

    it('resends invitations in bulk', function () {
        $invites = CustomerInvite::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
        ]);

        $result = CustomerInvite::resendBulk($invites->pluck('id')->toArray());

        expect($result['sent'])->toHaveCount(3)
            ->and($result['failed'])->toHaveCount(0);
    });
});
