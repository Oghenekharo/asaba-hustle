<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function notifications(Request $request)
    {
        $query = UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->latest('id');

        if (!$request->expectsJson()) {
            return view('web.notifications', [
                'notifications' => $query->cursorPaginate(20),
                'unreadNotificationsCount' => (int) $request->user()
                    ->notifications()
                    ->where('is_read', false)
                    ->count(),
            ]);
        }

        $notifications = $query
            ->limit(12)
            ->get();

        return $this->successResponse(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully.'
        );
    }

    public function markNotificationRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:user_notifications,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('The given data was invalid.', 422, $validator->errors()->toArray());
        }

        $notification = UserNotification::query()
            ->where('id', $request->input('notification_id'))
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return $this->successResponse(null, 'Notification marked as read.');
    }

    public function markAllNotificationsRead(Request $request)
    {
        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->update(['is_read' => true]);

        return $this->successResponse(null, 'All notifications marked as read.');
    }
}
