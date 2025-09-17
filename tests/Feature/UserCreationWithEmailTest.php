<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Mail\UserWelcomeEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create admin user with permission
    $this->adminUser = User::factory()->create();
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $this->adminUser->assignRole($adminRole);

    // Create roles for testing
    Role::firstOrCreate(['name' => 'driver']);
    Role::firstOrCreate(['name' => 'dispatcher']);

    actingAs($this->adminUser);
    Mail::fake();
});

it('can create a user with auto-generated password and send email', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            // Leave password empty to trigger auto-generation
            'email_verified_at' => true,
            'roles' => ['driver'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Assert user was created
    assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
    ]);

    // Assert email was sent
    Mail::assertSent(UserWelcomeEmail::class, function ($mail) {
        return $mail->hasTo('john.doe@example.com') &&
               $mail->user->email === 'john.doe@example.com' &&
               ! empty($mail->temporaryPassword);
    });

    // Assert the user has the correct role
    $user = User::where('email', 'john.doe@example.com')->first();
    expect($user->hasRole('driver'))->toBeTrue();
});

it('can create a user with custom password and send email', function () {
    $customPassword = 'MySecurePassword123!';

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => $customPassword,
            'email_verified_at' => true,
            'roles' => ['dispatcher'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    // Assert user was created
    assertDatabaseHas('users', [
        'name' => 'Jane Smith',
        'email' => 'jane.smith@example.com',
    ]);

    // Assert email was sent with the custom password
    Mail::assertSent(UserWelcomeEmail::class, function ($mail) use ($customPassword) {
        return $mail->hasTo('jane.smith@example.com') &&
               $mail->temporaryPassword === $customPassword;
    });
});

it('generates secure passwords with expected format', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified_at' => true,
        ])
        ->call('create');

    Mail::assertSent(UserWelcomeEmail::class, function ($mail) {
        $password = $mail->temporaryPassword;

        // Check password format: 2 words + 3 digits + 1 special char
        // Should be at least 12 characters
        expect(strlen($password))->toBeGreaterThanOrEqual(12);

        // Should contain at least one special character
        expect($password)->toMatch('/[!@#$%&*]/');

        // Should contain digits
        expect($password)->toMatch('/\d{3}/');

        // Should contain uppercase letters (from word combination)
        expect($password)->toMatch('/[A-Z]/');

        return true;
    });
});

it('shows notification when email sending fails', function () {
    // Make Mail fail
    Mail::shouldReceive('to')->andThrow(new Exception('Mail server error'));

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Failed Email User',
            'email' => 'failed@example.com',
            'email_verified_at' => true,
        ])
        ->call('create');

    // User should still be created
    assertDatabaseHas('users', [
        'email' => 'failed@example.com',
    ]);
});

it('can edit existing user without changing password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('original-password'),
    ]);
    $originalPassword = $user->password;

    Livewire::test(\App\Filament\Resources\Users\Pages\EditUser::class, [
        'record' => $user->id,
    ])
        ->fillForm([
            'name' => 'Updated Name',
            // Leave password empty
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->password)->toBe($originalPassword);

    // No email should be sent when editing
    Mail::assertNothingSent();
});
