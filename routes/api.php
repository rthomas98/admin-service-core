<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuoteController;

// Public API routes
Route::prefix('quotes')->group(function () {
    Route::post('/', [QuoteController::class, 'store']);
    Route::post('/liv-transport', [QuoteController::class, 'storeLivTransport']);
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});