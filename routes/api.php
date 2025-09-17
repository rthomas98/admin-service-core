<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerAccountController;
use App\Http\Controllers\Api\CustomerDashboardController;
use App\Http\Controllers\Api\CustomerInviteApiController;
use App\Http\Controllers\Api\CustomerInvoiceController;
use App\Http\Controllers\Api\CustomerNotificationController;
use App\Http\Controllers\Api\CustomerServiceRequestController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DriverAssignmentController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\FuelLogController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\VehicleInspectionController;
use App\Http\Controllers\Api\VehicleMaintenanceController;
use App\Http\Controllers\Api\WorkOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Customer Invite API Routes (Admin only)
Route::prefix('customer-invites')->name('api.customer-invites.')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CustomerInviteApiController::class, 'index'])->name('index');
    Route::post('/', [CustomerInviteApiController::class, 'store'])->name('store');
    Route::get('/statistics', [CustomerInviteApiController::class, 'statistics'])->name('statistics');
    Route::post('/bulk', [CustomerInviteApiController::class, 'bulkCreate'])->name('bulk-create');
    Route::post('/extend-expiration', [CustomerInviteApiController::class, 'extendExpiration'])->name('extend-expiration');
    Route::post('/cleanup', [CustomerInviteApiController::class, 'cleanup'])->name('cleanup');
    Route::get('/{invite}', [CustomerInviteApiController::class, 'show'])->name('show');
    Route::post('/{invite}/resend', [CustomerInviteApiController::class, 'resend'])->name('resend');
    Route::delete('/{invite}', [CustomerInviteApiController::class, 'destroy'])->name('destroy');
});

// Customer Portal API Routes
Route::prefix('customer')->name('api.customer.')->middleware('auth:customer')->group(function () {
    // Dashboard endpoints
    Route::get('dashboard/stats', [CustomerDashboardController::class, 'stats'])->name('dashboard.stats');
    Route::get('dashboard/recent-activity', [CustomerDashboardController::class, 'recentActivity'])->name('dashboard.recent-activity');
    Route::get('dashboard/quick-actions', [CustomerDashboardController::class, 'quickActions'])->name('dashboard.quick-actions');

    // Invoice management
    Route::apiResource('invoices', CustomerInvoiceController::class)->only(['index', 'show']);
    Route::get('invoices/{invoice}/download', [CustomerInvoiceController::class, 'download'])->name('invoices.download');
    Route::get('invoices/{invoice}/payments', [CustomerInvoiceController::class, 'payments'])->name('invoices.payments');

    // Service request management
    Route::apiResource('service-requests', CustomerServiceRequestController::class)->except(['destroy']);
    Route::patch('service-requests/{serviceRequest}/cancel', [CustomerServiceRequestController::class, 'cancel'])->name('service-requests.cancel');
    Route::post('service-requests/{serviceRequest}/attachments', [CustomerServiceRequestController::class, 'addAttachment'])->name('service-requests.attachments.add');
    Route::post('service-requests/{serviceRequest}/comments', [CustomerServiceRequestController::class, 'addComment'])->name('service-requests.comments.add');
    Route::get('service-requests/attachments/{attachment}/download', [CustomerServiceRequestController::class, 'downloadAttachment'])->name('service-requests.attachments.download');

    // Account management
    Route::get('account/profile', [CustomerAccountController::class, 'profile'])->name('account.profile');
    Route::patch('account/profile', [CustomerAccountController::class, 'updateProfile'])->name('account.update-profile');
    Route::patch('account/password', [CustomerAccountController::class, 'updatePassword'])->name('account.update-password');
    Route::patch('account/notifications', [CustomerAccountController::class, 'updateNotifications'])->name('account.update-notifications');

    // Notifications
    Route::get('notifications', [CustomerNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [CustomerNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('notifications/mark-all-read', [CustomerNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('notifications/{notification}', [CustomerNotificationController::class, 'destroy'])->name('notifications.destroy');
});
