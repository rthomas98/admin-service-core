<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\DriverAssignmentController;
use App\Http\Controllers\Api\VehicleInspectionController;
use App\Http\Controllers\Api\FuelLogController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\VehicleMaintenanceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DocumentController;

// Public API routes
Route::prefix('quotes')->group(function () {
    Route::post('/', [QuoteController::class, 'store']);
    Route::post('/liv-transport', [QuoteController::class, 'storeLivTransport']);
});

// Field App Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/companies', [AuthController::class, 'companies']);
    
    // Password Reset routes (public)
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/validate-token', [ForgotPasswordController::class, 'validateToken']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

// Protected API routes for Field App
Route::middleware('auth:sanctum')->group(function () {
    // Work Orders
    Route::prefix('work-orders')->group(function () {
        Route::get('/', [WorkOrderController::class, 'index']);
        Route::get('/{id}', [WorkOrderController::class, 'show']);
        Route::put('/{id}/status', [WorkOrderController::class, 'updateStatus']);
        Route::post('/{id}/signature', [WorkOrderController::class, 'addSignature']);
        Route::post('/{id}/photos', [WorkOrderController::class, 'addPhotos']);
        Route::put('/{id}/complete', [WorkOrderController::class, 'complete']);
    });
    
    // Driver Assignments
    Route::prefix('driver-assignments')->group(function () {
        Route::get('/', [DriverAssignmentController::class, 'index']);
        Route::get('/{id}', [DriverAssignmentController::class, 'show']);
        Route::put('/{id}/start', [DriverAssignmentController::class, 'start']);
        Route::put('/{id}/complete', [DriverAssignmentController::class, 'complete']);
        Route::post('/{id}/mileage', [DriverAssignmentController::class, 'updateMileage']);
    });
    
    // Vehicle Inspections
    Route::prefix('vehicle-inspections')->group(function () {
        Route::get('/', [VehicleInspectionController::class, 'index']);
        Route::get('/checklist', [VehicleInspectionController::class, 'getChecklist']);
        Route::post('/', [VehicleInspectionController::class, 'store']);
        Route::get('/{id}', [VehicleInspectionController::class, 'show']);
        Route::post('/{id}/correct-defects', [VehicleInspectionController::class, 'correctDefects']);
    });
    
    // Fuel Logs
    Route::prefix('fuel-logs')->group(function () {
        Route::get('/', [FuelLogController::class, 'index']);
        Route::get('/statistics', [FuelLogController::class, 'statistics']);
        Route::get('/nearby-stations', [FuelLogController::class, 'nearbyStations']);
        Route::post('/', [FuelLogController::class, 'store']);
        Route::get('/{id}', [FuelLogController::class, 'show']);
    });
    
    // Vehicle Maintenance
    Route::prefix('vehicle-maintenance')->group(function () {
        Route::get('/', [VehicleMaintenanceController::class, 'index']);
        Route::get('/upcoming', [VehicleMaintenanceController::class, 'upcoming']);
        Route::post('/report-issue', [VehicleMaintenanceController::class, 'reportIssue']);
        Route::get('/{id}', [VehicleMaintenanceController::class, 'show']);
    });
    
    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('/change-password', [ProfileController::class, 'changePassword']);
        Route::post('/upload-photo', [ProfileController::class, 'uploadPhoto']);
    });
    
    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/preferences', [NotificationController::class, 'preferences']);
        Route::put('/preferences', [NotificationController::class, 'updatePreferences']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::get('/{id}', [NotificationController::class, 'show']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
    });
    
    // Documents & Training
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::post('/upload', [DocumentController::class, 'upload']);
        Route::get('/training', [DocumentController::class, 'training']);
    });
    
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});