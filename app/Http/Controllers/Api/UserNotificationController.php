<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    /**
     * List user notifications
     */
    public function index(Request $request)
    {
        $notifications = UserNotification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return $this->successResponse(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully.'
        );
    }


    /**
     * Mark single notification as read
     */
    public function markRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:user_notifications,id'
        ]);

        $notification = UserNotification::where('id', $request->notification_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->update([
            'is_read' => true
        ]);

        return $this->successResponse(
            null,
            'Notification marked as read.'
        );
    }


    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request)
    {
        UserNotification::where('user_id', $request->user()->id)
            ->update([
                'is_read' => true
            ]);

        return $this->successResponse(
            null,
            'All notifications marked as read.'
        );
    }
}
