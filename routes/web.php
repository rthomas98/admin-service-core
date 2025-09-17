<?php

use App\Http\Controllers\CompanyUserAuthController;
use App\Http\Controllers\CompanyUserInviteController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerSetupController;
use App\Http\Controllers\TeamInviteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // If user is authenticated, redirect to admin dashboard
    if (auth()->check()) {
        return redirect('/admin');
    }

    // Otherwise, redirect to admin login
    return redirect('/admin/login');
})->name('home');

// Dashboard route removed - using Filament admin dashboard only

// Customer Setup Routes (authenticated users with company role)
Route::middleware(['auth'])->group(function () {
    Route::get('/customer/setup', [CustomerSetupController::class, 'show'])
        ->name('customer.setup');
    Route::post('/customer/setup', [CustomerSetupController::class, 'store'])
        ->name('customer.setup.store');
});

// Company Portal Routes (Multi-tenant company users)
Route::prefix('company-portal/{companySlug}')->name('company.')->group(function () {
    // Guest routes (not authenticated)
    Route::middleware('guest:company')->group(function () {
        Route::get('login', [CompanyUserAuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('login', [CompanyUserAuthController::class, 'login'])
            ->name('login.store');
    });

    // Authenticated company user routes
    Route::middleware('auth:company')->group(function () {
        Route::get('dashboard', [CompanyUserAuthController::class, 'showDashboard'])
            ->name('dashboard');
        Route::post('logout', [CompanyUserAuthController::class, 'logout'])
            ->name('logout');
    });
});

// Company User Invitation Routes
Route::prefix('company-portal')->group(function () {
    Route::get('accept-invite/{token}', [CompanyUserInviteController::class, 'show'])
        ->name('company.invite.show');
    Route::post('accept-invite/{token}', [CompanyUserInviteController::class, 'accept'])
        ->name('company.invite.accept');
});

// Customer Portal Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes (not authenticated)
    Route::middleware('guest:customer')->group(function () {
        Route::get('register/{token}', [CustomerAuthController::class, 'showRegistrationForm'])
            ->name('register.form');
        Route::post('register', [CustomerAuthController::class, 'register'])
            ->name('register');
        Route::get('login', [CustomerAuthController::class, 'showLoginForm'])
            ->name('login');
        Route::post('login', [CustomerAuthController::class, 'login'])
            ->name('login.store');
    });

    // Authenticated customer routes
    Route::middleware('auth:customer')->group(function () {
        Route::get('dashboard', [CustomerAuthController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('invoices', function () {
            return Inertia::render('Customer/Invoices');
        })->name('invoices');
        Route::get('invoices/{invoice}', [CustomerAuthController::class, 'showInvoice'])
            ->name('invoices.show');
        Route::get('service-requests', function () {
            return Inertia::render('Customer/ServiceRequests');
        })->name('service-requests');
        Route::get('notifications', function () {
            return Inertia::render('Customer/Notifications');
        })->name('notifications');
        Route::get('account', function () {
            return Inertia::render('Customer/Account');
        })->name('account');
        Route::post('logout', [CustomerAuthController::class, 'logout'])
            ->name('logout');
    });
});

// Team Invitation Routes
Route::middleware('guest')->group(function () {
    Route::get('/team/register/{token}', [TeamInviteController::class, 'showRegistrationForm'])
        ->name('team.register');
    Route::post('/team/register', [TeamInviteController::class, 'register'])
        ->name('team.register.store');
});

// Development pages (only in local environment)
if (app()->environment('local')) {
    Route::get('/dev/mail', function () {
        return view('mail-dev');
    })->name('dev.mail');

    Route::get('/dev/clipboard-test', function () {
        return view('test-clipboard');
    })->name('dev.clipboard-test');
}

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
