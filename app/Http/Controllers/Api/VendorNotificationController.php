<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class VendorNotificationController extends Controller
{
    // List notifications for the logged-in vendor user
    public function index(Request $request)
    {
        $user = auth()->user();

        // Standard notification schema usually relies on user_id
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched.',
            'data' => [
                'unread_count' => Notification::where('user_id', $user->id)->where('is_read', false)->count(),
                'notifications' => $notifications
            ]
        ], 200);
    }

    // Mark specific or all notifications as read
    public function markRead(Request $request)
    {
        $user = auth()->user();

        if ($request->has('notification_id')) {
            $notification = Notification::where('user_id', $user->id)
                ->where('id', $request->notification_id)
                ->first();
            
            if ($notification) {
                $notification->is_read = true;
                $notification->save();
            }
        } else {
            // Mark all as read
            Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Notifications marked as read.'
        ], 200);
    }
}
