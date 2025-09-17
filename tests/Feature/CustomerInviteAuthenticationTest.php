<?php

use App\Mail\CustomerInvitationMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerInvite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia;

beforeEach(function () {
    Mail::fake();
    $this->company = Company::factory()->create();
    $this->customer = Customer::factory()->for($this->company)->create();
    $this->admin = User::factory()->create();
});

describe('Customer Invite Authentication Flow', function () {

    it('displays invitation acceptance page with valid token', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'token' => 'valid-token-123',
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->get("/customer/accept-invite/{$invite->token}");

        $response->assertSuccessful()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('auth/AcceptInvite')
                ->has('invite')
                ->where('invite.email', $invite->email)
                ->where('invite.customer_id', $invite->customer_id)
            );
    });

    it('rejects expired invitation token', function () {
        $invite = CustomerInvite::factory()->create([
            'token' => 'expired-token',
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->get("/customer/accept-invite/{$invite->token}");

        $response->assertRedirect('/customer/login')
            ->assertSessionHas('error', 'This invitation has expired.');
    });

    it('rejects already accepted invitation', function () {
        $invite = CustomerInvite::factory()->create([
            'token' => 'accepted-token',
            'accepted_at' => now(),
            'is_active' => false,
        ]);

        $response = $this->get("/customer/accept-invite/{$invite->token}");

        $response->assertRedirect('/customer/login')
            ->assertSessionHas('error', 'This invitation has already been used.');
    });

    it('rejects inactive invitation', function () {
        $invite = CustomerInvite::factory()->create([
            'token' => 'inactive-token',
            'is_active' => false,
        ]);

        $response = $this->get("/customer/accept-invite/{$invite->token}");

        $response->assertRedirect('/customer/login')
            ->assertSessionHas('error', 'This invitation is no longer valid.');
    });

    it('creates customer portal account from invitation', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'newuser@example.com',
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => true,
        ]);

        $response->assertRedirect('/customer/dashboard');

        // Check customer was updated
        $this->customer->refresh();
        expect($this->customer)
            ->portal_access->toBeTrue()
            ->portal_password->not->toBeNull()
            ->email_verified_at->not->toBeNull();

        // Check invite was marked as accepted
        $invite->refresh();
        expect($invite)
            ->accepted_at->not->toBeNull()
            ->is_active->toBeFalse();

        // Check user is authenticated
        $this->assertAuthenticatedAs($this->customer, 'customer');
    });

    it('validates password requirements', function () {
        $invite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'weak',
            'password_confirmation' => 'weak',
            'terms' => true,
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('requires password confirmation match', function () {
        $invite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass123!',
            'terms' => true,
        ]);

        $response->assertSessionHasErrors(['password']);
    });

    it('requires terms acceptance', function () {
        $invite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => false,
        ]);

        $response->assertSessionHasErrors(['terms']);
    });

    it('prevents duplicate registration from same invite', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        // First registration
        $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => true,
        ]);

        // Logout
        auth('customer')->logout();

        // Try to register again with same invite
        $response = $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'Jane Doe',
            'password' => 'AnotherPass123!',
            'password_confirmation' => 'AnotherPass123!',
            'terms' => true,
        ]);

        $response->assertRedirect('/customer/login')
            ->assertSessionHas('error', 'This invitation has already been used.');
    });

    it('rate limits invitation acceptance attempts', function () {
        $invite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        // Clear any existing rate limits
        RateLimiter::clear('accept-invite:'.request()->ip());

        // Make multiple failed attempts
        for ($i = 0; $i < 6; $i++) {
            $this->post("/customer/accept-invite/{$invite->token}", [
                'name' => 'John Doe',
                'password' => 'wrong',
                'password_confirmation' => 'wrong',
                'terms' => true,
            ]);
        }

        // Next attempt should be rate limited
        $response = $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => true,
        ]);

        $response->assertStatus(429); // Too Many Requests
    });

    it('logs customer in after successful registration', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => true,
        ]);

        $this->assertAuthenticatedAs($this->customer, 'customer');
    });

    it('redirects authenticated customers away from invite page', function () {
        $invite = CustomerInvite::factory()->create([
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        // Login as a customer
        $existingCustomer = Customer::factory()->create([
            'portal_access' => true,
            'portal_password' => Hash::make('password'),
        ]);
        $this->actingAs($existingCustomer, 'customer');

        $response = $this->get("/customer/accept-invite/{$invite->token}");

        $response->assertRedirect('/customer/dashboard');
    });

    it('sends welcome email after successful registration', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'welcome@example.com',
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => true,
        ]);

        Mail::assertQueued(\App\Mail\CustomerWelcomeMail::class, function ($mail) {
            return $mail->hasTo('welcome@example.com');
        });
    });

    it('tracks invitation acceptance in activity log', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => 'John Doe',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => true,
        ]);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => CustomerInvite::class,
            'subject_id' => $invite->id,
            'description' => 'Invitation accepted',
        ]);
    });

    it('handles XSS attempts in registration data', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'is_active' => true,
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->post("/customer/accept-invite/{$invite->token}", [
            'name' => '<script>alert("XSS")</script>',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'terms' => true,
        ]);

        $response->assertRedirect('/customer/dashboard');

        $this->customer->refresh();
        expect($this->customer->name)->not->toContain('<script>');
    });

    it('handles SQL injection attempts in token', function () {
        $response = $this->get("/customer/accept-invite/'; DROP TABLE customer_invites; --");

        $response->assertNotFound();

        // Verify table still exists
        $this->assertDatabaseCount('customer_invites', CustomerInvite::count());
    });
});

describe('Customer Invite Resend', function () {

    it('resends invitation email', function () {
        $invite = CustomerInvite::factory()->create([
            'customer_id' => $this->customer->id,
            'email' => 'resend@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post("/admin/customer-invites/{$invite->id}/resend");

        $response->assertRedirect()
            ->assertSessionHas('success', 'Invitation resent successfully.');

        Mail::assertQueued(CustomerInvitationMail::class, function ($mail) {
            return $mail->hasTo('resend@example.com');
        });
    });

    it('regenerates token when resending', function () {
        $invite = CustomerInvite::factory()->create([
            'token' => 'old-token',
            'is_active' => true,
        ]);

        $originalToken = $invite->token;

        $this->actingAs($this->admin);
        $this->post("/admin/customer-invites/{$invite->id}/resend");

        $invite->refresh();
        expect($invite->token)->not->toBe($originalToken);
    });

    it('extends expiration when resending', function () {
        $invite = CustomerInvite::factory()->create([
            'expires_at' => now()->addHour(),
            'is_active' => true,
        ]);

        $originalExpiry = $invite->expires_at;

        $this->actingAs($this->admin);
        $this->post("/admin/customer-invites/{$invite->id}/resend");

        $invite->refresh();
        expect($invite->expires_at)->toBeGreaterThan($originalExpiry);
    });

    it('prevents resending accepted invitations', function () {
        $invite = CustomerInvite::factory()->create([
            'accepted_at' => now(),
            'is_active' => false,
        ]);

        $this->actingAs($this->admin);

        $response = $this->post("/admin/customer-invites/{$invite->id}/resend");

        $response->assertRedirect()
            ->assertSessionHas('error', 'Cannot resend an accepted invitation.');

        Mail::assertNothingQueued();
    });

    it('rate limits resend attempts', function () {
        $invite = CustomerInvite::factory()->create([
            'is_active' => true,
        ]);

        $this->actingAs($this->admin);

        // Clear rate limiter
        RateLimiter::clear('resend-invite:'.$invite->id);

        // Make multiple resend attempts
        for ($i = 0; $i < 4; $i++) {
            $this->post("/admin/customer-invites/{$invite->id}/resend");
        }

        // Next attempt should be rate limited
        $response = $this->post("/admin/customer-invites/{$invite->id}/resend");

        $response->assertStatus(429);
    });
});

describe('Customer Portal Login After Invitation', function () {

    it('allows login with portal credentials', function () {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'portal@example.com',
            'portal_access' => true,
            'portal_password' => Hash::make('MyPassword123!'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/customer/login', [
            'email' => 'portal@example.com',
            'password' => 'MyPassword123!',
        ]);

        $response->assertRedirect('/customer/dashboard');
        $this->assertAuthenticatedAs($customer, 'customer');
    });

    it('rejects login without portal access', function () {
        $customer = Customer::factory()->create([
            'email' => 'noaccess@example.com',
            'portal_access' => false,
            'portal_password' => Hash::make('MyPassword123!'),
        ]);

        $response = $this->post('/customer/login', [
            'email' => 'noaccess@example.com',
            'password' => 'MyPassword123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('customer');
    });

    it('rejects login with unverified email', function () {
        $customer = Customer::factory()->create([
            'email' => 'unverified@example.com',
            'portal_access' => true,
            'portal_password' => Hash::make('MyPassword123!'),
            'email_verified_at' => null,
        ]);

        $response = $this->post('/customer/login', [
            'email' => 'unverified@example.com',
            'password' => 'MyPassword123!',
        ]);

        $response->assertRedirect('/customer/verify-email');
    });

    it('tracks failed login attempts', function () {
        $customer = Customer::factory()->create([
            'email' => 'tracked@example.com',
            'portal_access' => true,
            'portal_password' => Hash::make('correct-password'),
        ]);

        // Make failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->post('/customer/login', [
                'email' => 'tracked@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->assertDatabaseHas('login_attempts', [
            'email' => 'tracked@example.com',
            'guard' => 'customer',
        ]);
    });

    it('locks account after too many failed attempts', function () {
        $customer = Customer::factory()->create([
            'email' => 'locked@example.com',
            'portal_access' => true,
            'portal_password' => Hash::make('correct-password'),
        ]);

        // Clear any existing lockout
        RateLimiter::clear('login:customer:locked@example.com');

        // Make multiple failed attempts
        for ($i = 0; $i < 6; $i++) {
            $this->post('/customer/login', [
                'email' => 'locked@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // Try with correct password
        $response = $this->post('/customer/login', [
            'email' => 'locked@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertStatus(429);
        $this->assertGuest('customer');
    });

    it('supports remember me functionality', function () {
        $customer = Customer::factory()->create([
            'email' => 'remember@example.com',
            'portal_access' => true,
            'portal_password' => Hash::make('MyPassword123!'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/customer/login', [
            'email' => 'remember@example.com',
            'password' => 'MyPassword123!',
            'remember' => true,
        ]);

        $response->assertRedirect('/customer/dashboard')
            ->assertCookie('remember_customer_'.$customer->id);
    });
});
