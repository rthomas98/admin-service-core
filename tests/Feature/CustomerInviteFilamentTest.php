<?php

use App\Filament\Resources\CustomerInviteResource;
use App\Filament\Resources\CustomerInviteResource\Pages\CreateCustomerInvite;
use App\Filament\Resources\CustomerInviteResource\Pages\EditCustomerInvite;
use App\Filament\Resources\CustomerInviteResource\Pages\ListCustomerInvites;
use App\Mail\CustomerInvitationMail;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerInvite;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    Mail::fake();
    $this->company = Company::factory()->create();
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);

    // Set up Filament tenant if needed
    if (method_exists(\Filament\Facades\Filament::class, 'setTenant')) {
        \Filament\Facades\Filament::setTenant($this->company);
    }
});

describe('CustomerInvite Filament Resource', function () {

    it('can render the list page', function () {
        $this->get(CustomerInviteResource::getUrl('index'))
            ->assertSuccessful()
            ->assertSee('Customer Invites');
    });

    it('can list customer invites', function () {
        $invites = CustomerInvite::factory()->count(5)->create([
            'company_id' => $this->company->id,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->assertCanSeeTableRecords($invites)
            ->assertCountTableRecords(5);
    });

    it('can filter by status', function () {
        $pending = CustomerInvite::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'accepted_at' => null,
        ]);

        $accepted = CustomerInvite::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'accepted_at' => now(),
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->filterTable('status', 'pending')
            ->assertCanSeeTableRecords($pending)
            ->assertCanNotSeeTableRecords($accepted)
            ->assertCountTableRecords(3);
    });

    it('can filter by expiration', function () {
        $expired = CustomerInvite::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'expires_at' => now()->subDay(),
        ]);

        $active = CustomerInvite::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'expires_at' => now()->addDay(),
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->filterTable('expired', true)
            ->assertCanSeeTableRecords($expired)
            ->assertCanNotSeeTableRecords($active)
            ->assertCountTableRecords(2);
    });

    it('can search invites by email', function () {
        $invite1 = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'john.doe@example.com',
        ]);

        $invite2 = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'jane.smith@example.com',
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->searchTable('john')
            ->assertCanSeeTableRecords([$invite1])
            ->assertCanNotSeeTableRecords([$invite2]);
    });

    it('can sort invites by created date', function () {
        $older = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'created_at' => now()->subDays(5),
        ]);

        $newer = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'created_at' => now(),
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->sortTable('created_at')
            ->assertCanSeeTableRecords([$older, $newer], inOrder: true)
            ->sortTable('created_at', 'desc')
            ->assertCanSeeTableRecords([$newer, $older], inOrder: true);
    });

    it('displays correct status badges', function () {
        $pending = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'accepted_at' => null,
            'expires_at' => now()->addDay(),
        ]);

        $accepted = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'accepted_at' => now(),
        ]);

        $expired = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'expires_at' => now()->subDay(),
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->assertTableColumnStateSet('status', 'pending', $pending)
            ->assertTableColumnStateSet('status', 'accepted', $accepted)
            ->assertTableColumnStateSet('status', 'expired', $expired);
    });

    it('can create a new invite', function () {
        $customer = Customer::factory()->for($this->company)->create();

        Livewire::test(CreateCustomerInvite::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'email' => 'newuser@example.com',
                'expires_at' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'send_email' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('customer_invites', [
            'customer_id' => $customer->id,
            'email' => 'newuser@example.com',
            'invited_by' => $this->admin->id,
        ]);

        Mail::assertQueued(CustomerInvitationMail::class);
    });

    it('validates email format on creation', function () {
        $customer = Customer::factory()->for($this->company)->create();

        Livewire::test(CreateCustomerInvite::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'email' => 'invalid-email',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    });

    it('prevents duplicate active invites', function () {
        $customer = Customer::factory()->for($this->company)->create();

        CustomerInvite::factory()->create([
            'customer_id' => $customer->id,
            'email' => 'existing@example.com',
            'is_active' => true,
        ]);

        Livewire::test(CreateCustomerInvite::class)
            ->fillForm([
                'customer_id' => $customer->id,
                'email' => 'existing@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['email']);
    });

    it('can edit an invite', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'old@example.com',
            'expires_at' => now()->addDay(),
        ]);

        Livewire::test(EditCustomerInvite::class, [
            'record' => $invite->getRouteKey(),
        ])
            ->fillForm([
                'email' => 'new@example.com',
                'expires_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $invite->refresh();
        expect($invite->email)->toBe('new@example.com');
    });

    it('can delete an invite', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Livewire::test(EditCustomerInvite::class, [
            'record' => $invite->getRouteKey(),
        ])
            ->callAction(DeleteAction::class)
            ->assertRedirect();

        $this->assertModelMissing($invite);
    });

    it('can bulk delete invites', function () {
        $invites = CustomerInvite::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->callTableBulkAction('delete', $invites)
            ->assertSuccessful();

        foreach ($invites as $invite) {
            $this->assertModelMissing($invite);
        }
    });

    it('can resend invitation', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'email' => 'resend@example.com',
            'token' => 'old-token',
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->callTableAction('resend', $invite)
            ->assertNotified();

        $invite->refresh();
        expect($invite->token)->not->toBe('old-token');

        Mail::assertQueued(CustomerInvitationMail::class, function ($mail) {
            return $mail->hasTo('resend@example.com');
        });
    });

    it('prevents resending accepted invitations', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'accepted_at' => now(),
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->assertTableActionDisabled('resend', $invite);
    });

    it('can deactivate an invite', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->callTableAction('deactivate', $invite)
            ->assertNotified();

        $invite->refresh();
        expect($invite->is_active)->toBeFalse();
    });

    it('can extend invitation expiry', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'expires_at' => now()->addDay(),
        ]);

        $originalExpiry = $invite->expires_at;

        Livewire::test(ListCustomerInvites::class)
            ->callTableAction('extend', $invite, data: [
                'days' => 7,
            ])
            ->assertNotified();

        $invite->refresh();
        expect($invite->expires_at)->toBeGreaterThan($originalExpiry);
    });

    it('shows invitation statistics widget', function () {
        CustomerInvite::factory()->count(5)->create([
            'company_id' => $this->company->id,
            'accepted_at' => now(),
        ]);

        CustomerInvite::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'accepted_at' => null,
        ]);

        CustomerInvite::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->get(CustomerInviteResource::getUrl('index'))
            ->assertSee('50%') // Acceptance rate
            ->assertSee('3') // Pending
            ->assertSee('5') // Accepted
            ->assertSee('2'); // Expired
    });

    it('exports invitations to CSV', function () {
        CustomerInvite::factory()->count(5)->create([
            'company_id' => $this->company->id,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->callAction('export')
            ->assertFileDownloaded('customer-invites-'.now()->format('Y-m-d').'.csv');
    });

    it('can bulk send invitations', function () {
        $customer1 = Customer::factory()->for($this->company)->create();
        $customer2 = Customer::factory()->for($this->company)->create();

        Livewire::test(ListCustomerInvites::class)
            ->callAction('bulkInvite', data: [
                'customers' => [$customer1->id, $customer2->id],
                'emails' => "user1@example.com\nuser2@example.com",
            ])
            ->assertNotified();

        $this->assertDatabaseCount('customer_invites', 2);
        Mail::assertQueued(CustomerInvitationMail::class, 2);
    });

    it('validates bulk invitation emails', function () {
        $customer = Customer::factory()->for($this->company)->create();

        Livewire::test(ListCustomerInvites::class)
            ->callAction('bulkInvite', data: [
                'customers' => [$customer->id],
                'emails' => "valid@example.com\ninvalid-email\nanother@example.com",
            ])
            ->assertActionHasErrors(['emails']);
    });

    it('handles invitation templates', function () {
        Livewire::test(CreateCustomerInvite::class)
            ->fillForm([
                'template' => 'welcome',
            ])
            ->assertFormFieldExists('message')
            ->assertFormFieldContains('message', 'Welcome to our customer portal');
    });

    it('enforces permission-based access', function () {
        $userWithoutPermission = User::factory()->create();
        $this->actingAs($userWithoutPermission);

        $this->get(CustomerInviteResource::getUrl('index'))
            ->assertForbidden();
    });

    it('tracks invitation activity', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->callTableAction('resend', $invite);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => CustomerInvite::class,
            'subject_id' => $invite->id,
            'description' => 'Invitation resent',
            'causer_id' => $this->admin->id,
        ]);
    });

    it('shows related customer information', function () {
        $customer = Customer::factory()->for($this->company)->create([
            'name' => 'Acme Corporation',
        ]);

        $invite = CustomerInvite::factory()->create([
            'customer_id' => $customer->id,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->assertCanSeeTableRecords([$invite])
            ->assertSee('Acme Corporation');
    });

    it('filters by customer', function () {
        $customer1 = Customer::factory()->for($this->company)->create();
        $customer2 = Customer::factory()->for($this->company)->create();

        $invites1 = CustomerInvite::factory()->count(2)->create([
            'customer_id' => $customer1->id,
        ]);

        $invites2 = CustomerInvite::factory()->count(3)->create([
            'customer_id' => $customer2->id,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->filterTable('customer_id', $customer1->id)
            ->assertCanSeeTableRecords($invites1)
            ->assertCanNotSeeTableRecords($invites2);
    });

    it('displays invitation URL', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
            'token' => 'test-token-123',
        ]);

        Livewire::test(EditCustomerInvite::class, [
            'record' => $invite->getRouteKey(),
        ])
            ->assertSee('/customer/accept-invite/test-token-123');
    });

    it('copies invitation URL to clipboard', function () {
        $invite = CustomerInvite::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Livewire::test(ListCustomerInvites::class)
            ->callTableAction('copyUrl', $invite)
            ->assertNotified('Invitation URL copied to clipboard');
    });
});
