<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function index(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $query = Notification::where('company_id', $driver->company_id)
            ->where(function($q) use ($driver, $request) {
                $q->where('recipient_id', $request->user()->id)
                  ->orWhere('recipient_type', 'all_drivers')
                  ->orWhere(function($query) use ($driver) {
                      $query->where('recipient_type', 'specific_drivers')
                            ->whereJsonContains('recipient_data->driver_ids', $driver->id);
                  });
            });

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Default to last 30 days
        if (!$request->has('all')) {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'category' => $notification->category,
                    'title' => $notification->subject,
                    'message' => $notification->message,
                    'status' => $notification->status,
                    'priority' => $notification->priority,
                    'is_read' => $notification->is_read,
                    'read_at' => $notification->read_at,
                    'action_url' => $notification->action_data['url'] ?? null,
                    'created_at' => $notification->created_at,
                ];
            });

        $unreadCount = $notifications->where('is_read', false)->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get single notification
     */
    public function show(Request $request, $id): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $notification = Notification::where('id', $id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        // Mark as read
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);
        }

        return response()->json([
            'notification' => [
                'id' => $notification->id,
                'type' => $notification->type,
                'category' => $notification->category,
                'title' => $notification->subject,
                'message' => $notification->message,
                'status' => $notification->status,
                'priority' => $notification->priority,
                'is_read' => $notification->is_read,
                'read_at' => $notification->read_at,
                'action_url' => $notification->action_data['url'] ?? null,
                'action_data' => $notification->action_data,
                'created_at' => $notification->created_at,
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $notification = Notification::where('id', $id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => Carbon::now(),
        ]);

        return response()->json([
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        Notification::where('company_id', $driver->company_id)
            ->where(function($q) use ($driver, $request) {
                $q->where('recipient_id', $request->user()->id)
                  ->orWhere('recipient_type', 'all_drivers')
                  ->orWhere(function($query) use ($driver) {
                      $query->where('recipient_type', 'specific_drivers')
                            ->whereJsonContains('recipient_data->driver_ids', $driver->id);
                  });
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => Carbon::now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Get notification preferences
     */
    public function preferences(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Get or create default preferences
        $preferences = $driver->notification_preferences ?? [
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
            'work_order_updates' => true,
            'schedule_changes' => true,
            'maintenance_reminders' => true,
            'safety_alerts' => true,
            'company_announcements' => true,
        ];

        return response()->json([
            'preferences' => $preferences
        ]);
    }

    /**
     * Update notification preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'work_order_updates' => 'boolean',
            'schedule_changes' => 'boolean',
            'maintenance_reminders' => 'boolean',
            'safety_alerts' => 'boolean',
            'company_announcements' => 'boolean',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $preferences = array_merge(
            $driver->notification_preferences ?? [],
            $request->only([
                'email_notifications',
                'push_notifications',
                'sms_notifications',
                'work_order_updates',
                'schedule_changes',
                'maintenance_reminders',
                'safety_alerts',
                'company_announcements',
            ])
        );

        $driver->update(['notification_preferences' => $preferences]);

        return response()->json([
            'message' => 'Notification preferences updated',
            'preferences' => $preferences
        ]);
    }
}