<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerNotificationController extends Controller
{
    /**
     * Display a listing of customer notifications
     */
    public function index(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $query = Notification::where('recipient_type', Customer::class)
            ->where('recipient_id', $customer->id);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'unread') {
                $query->whereNull('read_at');
            } elseif ($request->status === 'read') {
                $query->whereNotNull('read_at');
            }
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && ! empty($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && ! empty($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['created_at', 'read_at', 'category', 'type'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = min($request->get('per_page', 20), 50);
        $notifications = $query->paginate($perPage);

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'category' => $notification->category->value,
                    'category_label' => $notification->category->getLabel(),
                    'type' => $notification->type->value,
                    'type_label' => $notification->type->getLabel(),
                    'status' => $notification->status->value,
                    'status_label' => $notification->status->getLabel(),
                    'priority' => $notification->priority,
                    'data' => $notification->data ?? [],
                    'is_read' => ! is_null($notification->read_at),
                    'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                    'formatted_read_at' => $notification->read_at?->format('M j, Y g:i A'),
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'formatted_created_at' => $notification->created_at->format('M j, Y g:i A'),
                    'time_ago' => $notification->created_at->diffForHumans(),
                    'action_url' => $this->getNotificationActionUrl($notification),
                ];
            }),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
            'summary' => [
                'total_count' => $query->count(),
                'unread_count' => Notification::where('recipient_type', Customer::class)
                    ->where('recipient_id', $customer->id)
                    ->whereNull('read_at')
                    ->count(),
                'read_count' => Notification::where('recipient_type', Customer::class)
                    ->where('recipient_id', $customer->id)
                    ->whereNotNull('read_at')
                    ->count(),
            ],
            'filters' => [
                'status' => $request->get('status', 'all'),
                'category' => $request->get('category', 'all'),
                'type' => $request->get('type', 'all'),
                'search' => $request->get('search', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the notification belongs to the authenticated customer
        if ($notification->recipient_type !== Customer::class ||
            $notification->recipient_id !== $customer->id) {
            abort(404);
        }

        // Only mark as read if it's unread
        if (is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Notification marked as read',
            'notification' => [
                'id' => $notification->id,
                'is_read' => true,
                'read_at' => $notification->read_at->format('M j, Y g:i A'),
            ],
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $unreadCount = Notification::where('recipient_type', Customer::class)
            ->where('recipient_id', $customer->id)
            ->whereNull('read_at')
            ->count();

        if ($unreadCount > 0) {
            Notification::where('recipient_type', Customer::class)
                ->where('recipient_id', $customer->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => "Marked {$unreadCount} notifications as read",
            'marked_count' => $unreadCount,
        ]);
    }

    /**
     * Delete a specific notification
     */
    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the notification belongs to the authenticated customer
        if ($notification->recipient_type !== Customer::class ||
            $notification->recipient_id !== $customer->id) {
            abort(404);
        }

        try {
            $notification->delete();

            return response()->json([
                'message' => 'Notification deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting notification',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Get action URL for a notification based on its type and data
     */
    private function getNotificationActionUrl(Notification $notification): ?string
    {
        $data = $notification->data ?? [];

        return match ($notification->category->value) {
            'invoice' => isset($data['invoice_id']) ? "/customer/invoices/{$data['invoice_id']}" : '/customer/invoices',
            'service_request' => isset($data['service_request_id']) ? "/customer/service-requests/{$data['service_request_id']}" : '/customer/service-requests',
            'payment' => isset($data['invoice_id']) ? "/customer/invoices/{$data['invoice_id']}" : '/customer/invoices',
            'system' => '/customer/account',
            'security' => '/customer/account',
            'marketing' => null,
            default => null,
        };
    }
}
