<?php

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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Development mail viewer page (only in local environment)
if (app()->environment('local')) {
    Route::get('/dev/mail', function () {
        return view('mail-dev');
    })->name('dev.mail');
}

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
