<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\ServiceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CustomerDashboardController extends Controller
{
    /**
     * Get dashboard statistics for the customer
     */
    public function stats(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        $cacheKey = "customer_dashboard_stats_{$customer->id}";

        // Cache statistics for 5 minutes to improve performance
        $stats = Cache::remember($cacheKey, 300, function () use ($customer) {
            // Get invoice statistics
            $invoiceStats = Invoice::where('customer_id', $customer->id)
                ->selectRaw('
                    COUNT(*) as total_invoices,
                    COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_invoices,
                    COUNT(CASE WHEN status = "overdue" THEN 1 END) as overdue_invoices,
                    COUNT(CASE WHEN status = "paid" THEN 1 END) as paid_invoices,
                    COALESCE(SUM(CASE WHEN status != "paid" THEN balance_due ELSE 0 END), 0) as total_outstanding,
                    COALESCE(SUM(CASE WHEN status = "paid" THEN total_amount ELSE 0 END), 0) as total_paid
                ')
                ->first();

            // Get service request statistics
            $serviceRequestStats = ServiceRequest::where('customer_id', $customer->id)
                ->selectRaw('
                    COUNT(*) as total_requests,
                    COUNT(CASE WHEN status = "pending" THEN 1 END) as pending_requests,
                    COUNT(CASE WHEN status = "in_progress" THEN 1 END) as in_progress_requests,
                    COUNT(CASE WHEN status = "completed" THEN 1 END) as completed_requests,
                    COUNT(CASE WHEN status = "cancelled" THEN 1 END) as cancelled_requests,
                    COUNT(CASE WHEN status = "on_hold" THEN 1 END) as on_hold_requests
                ')
                ->first();

            return compact('invoiceStats', 'serviceRequestStats');
        });

        $invoiceStats = $stats['invoiceStats'];
        $serviceRequestStats = $stats['serviceRequestStats'];

        // Get notification count
        $unreadNotifications = Notification::where('recipient_type', Customer::class)
            ->where('recipient_id', $customer->id)
            ->where('read_at', null)
            ->count();

        // Get recent activity count (last 30 days)
        $recentActivityCount = collect([
            Invoice::where('customer_id', $customer->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            ServiceRequest::where('customer_id', $customer->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
        ])->sum();

        return response()->json([
            'invoices' => [
                'total' => $invoiceStats->total_invoices ?? 0,
                'pending' => $invoiceStats->pending_invoices ?? 0,
                'overdue' => $invoiceStats->overdue_invoices ?? 0,
                'paid' => $invoiceStats->paid_invoices ?? 0,
                'total_outstanding' => number_format($invoiceStats->total_outstanding ?? 0, 2),
                'total_paid' => number_format($invoiceStats->total_paid ?? 0, 2),
            ],
            'service_requests' => [
                'total' => $serviceRequestStats->total_requests ?? 0,
                'pending' => $serviceRequestStats->pending_requests ?? 0,
                'in_progress' => $serviceRequestStats->in_progress_requests ?? 0,
                'completed' => $serviceRequestStats->completed_requests ?? 0,
                'cancelled' => $serviceRequestStats->cancelled_requests ?? 0,
                'on_hold' => $serviceRequestStats->on_hold_requests ?? 0,
            ],
            'notifications' => [
                'unread_count' => $unreadNotifications,
            ],
            'activity' => [
                'recent_count' => $recentActivityCount,
            ],
        ]);
    }

    /**
     * Get recent activity for the customer
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        $limit = $request->get('limit', 10);

        // Get recent invoices with minimal data needed
        $recentInvoices = Invoice::where('customer_id', $customer->id)
            ->select('id', 'invoice_number', 'total_amount', 'status', 'created_at')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'type' => 'invoice',
                    'title' => "Invoice #{$invoice->invoice_number}",
                    'description' => 'Invoice for $'.number_format($invoice->total_amount, 2),
                    'status' => $invoice->status,
                    'date' => $invoice->created_at,
                    'formatted_date' => $invoice->created_at->format('M j, Y'),
                    'url' => "/customer/invoices/{$invoice->id}",
                ];
            });

        // Get recent service requests with minimal data needed
        $recentServiceRequests = ServiceRequest::where('customer_id', $customer->id)
            ->select('id', 'title', 'description', 'status', 'created_at')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'type' => 'service_request',
                    'title' => $request->title ?? 'Service Request',
                    'description' => $request->description,
                    'status' => $request->status->value,
                    'date' => $request->created_at,
                    'formatted_date' => $request->created_at->format('M j, Y'),
                    'url' => "/customer/service-requests/{$request->id}",
                ];
            });

        // Merge and sort by date
        $activities = $recentInvoices->concat($recentServiceRequests)
            ->sortByDesc('date')
            ->take($limit)
            ->values();

        return response()->json([
            'activities' => $activities,
        ]);
    }

    /**
     * Get quick actions available to the customer
     */
    public function quickActions(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Count items that need attention
        $pendingInvoices = Invoice::where('customer_id', $customer->id)
            ->where('status', 'pending')
            ->count();

        $overdueInvoices = Invoice::where('customer_id', $customer->id)
            ->where('status', 'overdue')
            ->count();

        $activeServiceRequests = ServiceRequest::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $unreadNotifications = Notification::where('recipient_type', Customer::class)
            ->where('recipient_id', $customer->id)
            ->where('read_at', null)
            ->count();

        $quickActions = [
            [
                'id' => 'new_service_request',
                'title' => 'New Service Request',
                'description' => 'Create a new service request',
                'icon' => 'plus-circle',
                'url' => '/customer/service-requests/create',
                'type' => 'primary',
                'badge' => null,
            ],
            [
                'id' => 'view_invoices',
                'title' => 'View Invoices',
                'description' => 'Review your invoices and payments',
                'icon' => 'document-text',
                'url' => '/customer/invoices',
                'type' => 'secondary',
                'badge' => $pendingInvoices + $overdueInvoices > 0 ? $pendingInvoices + $overdueInvoices : null,
            ],
            [
                'id' => 'service_requests',
                'title' => 'Service Requests',
                'description' => 'Track your service requests',
                'icon' => 'clipboard-list',
                'url' => '/customer/service-requests',
                'type' => 'secondary',
                'badge' => $activeServiceRequests > 0 ? $activeServiceRequests : null,
            ],
            [
                'id' => 'notifications',
                'title' => 'Notifications',
                'description' => 'View your notifications',
                'icon' => 'bell',
                'url' => '/customer/notifications',
                'type' => 'secondary',
                'badge' => $unreadNotifications > 0 ? $unreadNotifications : null,
            ],
            [
                'id' => 'account_settings',
                'title' => 'Account Settings',
                'description' => 'Manage your account preferences',
                'icon' => 'cog',
                'url' => '/customer/account',
                'type' => 'secondary',
                'badge' => null,
            ],
        ];

        return response()->json([
            'quick_actions' => $quickActions,
        ]);
    }
}
