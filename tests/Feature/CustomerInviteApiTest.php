<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerInvite;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::factory()->create(['name' => 'RAW Disposal']);
    $this->user = User::factory()->create(['current_company_id' => $this->company->id]);
    Sanctum::actingAs($this->user);
});

describe('Customer Invite API', function () {
    describe('Index Endpoint', function () {
        it('returns paginated list of invitations', function () {
            CustomerInvite::factory()->count(25)->create([
                'company_id' => $this->company->id,
            ]);

            $response = getJson('/api/customer-invites');

            $response->assertOk()
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'email',
                            'status',
                            'expires_at',
                            'is_active',
                            'created_at',
                        ],
                    ],
                    'links',
                    'meta',
                ]);

            expect($response->json('data'))->toHaveCount(15); // Default pagination
        });

        it('filters invitations by status', function () {
            // Create invitations with different statuses
            CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ]);

            CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->subDay(),
                'accepted_at' => null,
            ]);

            CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'accepted_at' => now(),
            ]);

            // Test pending filter
            $response = getJson('/api/customer-invites?status=pending');
            expect($response->json('data'))->toHaveCount(1);

            // Test expired filter
            $response = getJson('/api/customer-invites?status=expired');
            expect($response->json('data'))->toHaveCount(1);

            // Test accepted filter
            $response = getJson('/api/customer-invites?status=accepted');
            expect($response->json('data'))->toHaveCount(1);
        });

        it('filters invitations by customer', function () {
            $customer = Customer::factory()->create(['company_id' => $this->company->id]);
            CustomerInvite::factory()->count(3)->create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
            ]);
            CustomerInvite::factory()->count(2)->create([
                'company_id' => $this->company->id,
                'customer_id' => null,
            ]);

            $response = getJson("/api/customer-invites?customer_id={$customer->id}");

            expect($response->json('data'))->toHaveCount(3);
        });

        it('sorts invitations correctly', function () {
            $oldest = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'created_at' => now()->subDays(5),
            ]);

            $newest = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'created_at' => now(),
            ]);

            $response = getJson('/api/customer-invites?sort_by=created_at&sort_direction=asc');

            expect($response->json('data.0.id'))->toBe($oldest->id);

            $response = getJson('/api/customer-invites?sort_by=created_at&sort_direction=desc');

            expect($response->json('data.0.id'))->toBe($newest->id);
        });
    });

    describe('Store Endpoint', function () {
        it('creates a new invitation', function () {
            Mail::fake();

            $response = postJson('/api/customer-invites', [
                'email' => 'test@example.com',
                'customer_id' => null,
                'expires_at' => now()->addDays(7)->toIso8601String(),
                'send_email' => true,
            ]);

            $response->assertCreated()
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id',
                        'email',
                        'status',
                        'expires_at',
                    ],
                ]);

            $this->assertDatabaseHas('customer_invites', [
                'email' => 'test@example.com',
                'company_id' => $this->company->id,
                'invited_by' => $this->user->id,
            ]);

            Mail::assertSent(\App\Mail\CustomerInvitationMail::class);
        });

        it('validates required fields', function () {
            $response = postJson('/api/customer-invites', []);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('prevents duplicate active invitations', function () {
            CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'email' => 'existing@example.com',
                'is_active' => true,
                'expires_at' => now()->addDay(),
            ]);

            $response = postJson('/api/customer-invites', [
                'email' => 'existing@example.com',
            ]);

            $response->assertConflict()
                ->assertJson([
                    'message' => 'An active invitation already exists for this email address',
                ]);
        });

        it('does not send email when send_email is false', function () {
            Mail::fake();

            $response = postJson('/api/customer-invites', [
                'email' => 'test@example.com',
                'send_email' => false,
            ]);

            $response->assertCreated();
            Mail::assertNothingSent();
        });
    });

    describe('Show Endpoint', function () {
        it('returns invitation details', function () {
            $invite = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
            ]);

            $response = getJson("/api/customer-invites/{$invite->id}");

            $response->assertOk()
                ->assertJson([
                    'data' => [
                        'id' => $invite->id,
                        'email' => $invite->email,
                    ],
                ]);
        });

        it('returns 404 for non-existent invitation', function () {
            $response = getJson('/api/customer-invites/999999');

            $response->assertNotFound();
        });
    });

    describe('Resend Endpoint', function () {
        it('resends invitation email', function () {
            Mail::fake();

            $invite = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->addDay(),
            ]);

            $originalToken = $invite->token;

            $response = postJson("/api/customer-invites/{$invite->id}/resend");

            $response->assertOk()
                ->assertJson([
                    'message' => 'Invitation resent successfully',
                ]);

            $invite->refresh();
            expect($invite->token)->not->toBe($originalToken);
            expect($invite->expires_at)->toBeGreaterThan(now()->addDays(6));

            Mail::assertSent(\App\Mail\CustomerInvitationMail::class);
        });

        it('cannot resend accepted invitation', function () {
            $invite = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'accepted_at' => now(),
            ]);

            $response = postJson("/api/customer-invites/{$invite->id}/resend");

            $response->assertBadRequest()
                ->assertJson([
                    'message' => 'Cannot resend an accepted invitation',
                ]);
        });
    });

    describe('Destroy Endpoint', function () {
        it('cancels invitation', function () {
            $invite = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
            ]);

            $response = deleteJson("/api/customer-invites/{$invite->id}");

            $response->assertOk()
                ->assertJson([
                    'message' => 'Invitation cancelled successfully',
                ]);

            $this->assertDatabaseMissing('customer_invites', [
                'id' => $invite->id,
            ]);
        });

        it('cannot delete accepted invitation', function () {
            $invite = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'accepted_at' => now(),
            ]);

            $response = deleteJson("/api/customer-invites/{$invite->id}");

            $response->assertBadRequest()
                ->assertJson([
                    'message' => 'Cannot delete an accepted invitation',
                ]);

            $this->assertDatabaseHas('customer_invites', [
                'id' => $invite->id,
            ]);
        });
    });

    describe('Bulk Create Endpoint', function () {
        it('creates multiple invitations', function () {
            Mail::fake();

            $customer = Customer::factory()->create(['company_id' => $this->company->id]);

            $response = postJson('/api/customer-invites/bulk', [
                'customer_id' => $customer->id,
                'emails' => [
                    'test1@example.com',
                    'test2@example.com',
                    'test3@example.com',
                ],
                'send_emails' => true,
            ]);

            $response->assertCreated()
                ->assertJson([
                    'message' => 'Bulk invitations processed',
                    'created' => 3,
                    'skipped' => 0,
                ]);

            $this->assertDatabaseCount('customer_invites', 3);
            Mail::assertSentCount(3);
        });

        it('skips duplicate emails', function () {
            $customer = Customer::factory()->create(['company_id' => $this->company->id]);

            CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'email' => 'existing@example.com',
                'is_active' => true,
            ]);

            $response = postJson('/api/customer-invites/bulk', [
                'customer_id' => $customer->id,
                'emails' => [
                    'existing@example.com',
                    'new@example.com',
                ],
            ]);

            $response->assertCreated()
                ->assertJson([
                    'created' => 1,
                    'skipped' => 1,
                ]);
        });

        it('validates email array', function () {
            $customer = Customer::factory()->create(['company_id' => $this->company->id]);

            $response = postJson('/api/customer-invites/bulk', [
                'customer_id' => $customer->id,
                'emails' => [
                    'invalid-email',
                    'test@example.com',
                ],
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['emails.0']);
        });
    });

    describe('Statistics Endpoint', function () {
        it('returns invitation statistics', function () {
            // Create various invitations
            CustomerInvite::factory()->count(5)->create([
                'company_id' => $this->company->id,
                'accepted_at' => now(),
            ]);

            CustomerInvite::factory()->count(3)->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->addDays(7),
                'accepted_at' => null,
            ]);

            CustomerInvite::factory()->count(2)->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->subDay(),
                'accepted_at' => null,
            ]);

            $response = getJson('/api/customer-invites/statistics');

            $response->assertOk()
                ->assertJsonStructure([
                    'statistics' => [
                        'total',
                        'accepted',
                        'pending',
                        'expired',
                        'acceptance_rate',
                        'sent_today',
                        'sent_this_week',
                        'sent_this_month',
                        'expiring_soon',
                    ],
                ]);

            expect($response->json('statistics.total'))->toBe(10)
                ->and($response->json('statistics.accepted'))->toBe(5)
                ->and($response->json('statistics.pending'))->toBe(3)
                ->and($response->json('statistics.expired'))->toBe(2)
                ->and($response->json('statistics.acceptance_rate'))->toBe(50.0);
        });

        it('filters statistics by customer', function () {
            $customer = Customer::factory()->create(['company_id' => $this->company->id]);

            CustomerInvite::factory()->count(3)->create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'accepted_at' => now(),
            ]);

            CustomerInvite::factory()->count(2)->create([
                'company_id' => $this->company->id,
                'customer_id' => $customer->id,
                'accepted_at' => null,
            ]);

            $response = getJson("/api/customer-invites/statistics?customer_id={$customer->id}");

            expect($response->json('statistics.total'))->toBe(5)
                ->and($response->json('statistics.accepted'))->toBe(3)
                ->and($response->json('statistics.acceptance_rate'))->toBe(60.0);
        });
    });

    describe('Extend Expiration Endpoint', function () {
        it('extends expiration for multiple invitations', function () {
            $invites = CustomerInvite::factory()->count(3)->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->addDay(),
            ]);

            $response = postJson('/api/customer-invites/extend-expiration', [
                'invite_ids' => $invites->pluck('id')->toArray(),
                'days' => 7,
            ]);

            $response->assertOk()
                ->assertJson([
                    'message' => 'Extended expiration for 3 invitations',
                    'extended' => 3,
                    'failed' => [],
                ]);

            foreach ($invites as $invite) {
                $invite->refresh();
                expect($invite->expires_at)->toBeGreaterThan(now()->addDays(7));
            }
        });

        it('skips accepted invitations', function () {
            $pending = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->addDay(),
            ]);

            $accepted = CustomerInvite::factory()->create([
                'company_id' => $this->company->id,
                'accepted_at' => now(),
            ]);

            $response = postJson('/api/customer-invites/extend-expiration', [
                'invite_ids' => [$pending->id, $accepted->id],
                'days' => 7,
            ]);

            $response->assertOk()
                ->assertJson([
                    'extended' => 1,
                    'failed' => [$accepted->id],
                ]);
        });
    });

    describe('Cleanup Endpoint', function () {
        it('cleans up expired invitations', function () {
            CustomerInvite::factory()->count(3)->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->subDay(),
                'is_active' => true,
            ]);

            CustomerInvite::factory()->count(2)->create([
                'company_id' => $this->company->id,
                'expires_at' => now()->addDay(),
                'is_active' => true,
            ]);

            $response = postJson('/api/customer-invites/cleanup');

            $response->assertOk()
                ->assertJson([
                    'message' => 'Cleaned up 3 expired invitations',
                    'count' => 3,
                ]);

            $this->assertDatabaseCount('customer_invites', 5);

            // Check that expired invitations are now inactive
            $expired = CustomerInvite::expired()->get();
            expect($expired)->each->is_active->toBe(false);
        });
    });
});

describe('Authentication and Authorization', function () {
    it('requires authentication', function () {
        // Logout the user
        auth()->logout();

        $response = getJson('/api/customer-invites');

        $response->assertUnauthorized();
    });

    it('respects company context', function () {
        $otherCompany = Company::factory()->create();
        $otherInvite = CustomerInvite::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $ownInvite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
        ]);

        // Should only see own company's invitations
        $response = getJson('/api/customer-invites');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        expect($ids)->toContain($ownInvite->id)
            ->not->toContain($otherInvite->id);
    });
});
