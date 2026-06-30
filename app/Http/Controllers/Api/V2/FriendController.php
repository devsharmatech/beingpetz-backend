<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\V2FriendRequestLog;
use App\Models\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Notification;
use App\Services\FirebaseService;

class FriendController extends Controller
{
    protected $fcm;

public function __construct()
{
    $this->fcm = new FirebaseService(
        env('FIREBASE_PROJECT_ID'),
        public_path(env('FIREBASE_CREDENTIALS_PATH'))
    );
}

    /**
     * Get friend request logs (last 5 sent and 5 received).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestLogs(Request $request)
    {
        try {
            $user = $request->user();

            // Get last 5 sent friend requests
            $sentRequests = V2FriendRequestLog::with(['receiver:id,first_name,last_name,username,profile'])
                ->sentBy($user->id)
                ->recent(5)
                ->get();

            // Get last 5 received friend requests
            $receivedRequests = V2FriendRequestLog::with(['sender:id,first_name,last_name,username,profile'])
                ->receivedBy($user->id)
                ->recent(5)
                ->get();

            // The friend requests are now in a unified table, so no sync is needed.
            return response()->json([
                'success' => true,
                'data' => [
                    'sent' => $sentRequests->map(function ($request) {
                        return $this->formatSentRequestResponse($request);
                    }),
                    'received' => $receivedRequests->map(function ($request) {
                        return $this->formatReceivedRequestResponse($request);
                    }),
                    'summary' => [
                        'total_sent' => V2FriendRequestLog::sentBy($user->id)->count(),
                        'total_received' => V2FriendRequestLog::receivedBy($user->id)->count(),
                        'pending_sent' => V2FriendRequestLog::sentBy($user->id)->pending()->count(),
                        'pending_received' => V2FriendRequestLog::receivedBy($user->id)->pending()->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve friend request logs.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function unfriend(Request $request)
{
    $validator = Validator::make($request->all(), [
        'friend_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    DB::beginTransaction();

    try {

        $user = $request->user();

        if ($user->id == $request->friend_id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot unfriend yourself.'
            ], 400);
        }

        $friend = User::find($request->friend_id);

        // Check friendship
        $friendship = FriendRequest::where(function ($q) use ($user, $friend) {
                $q->where('from_parent_id', $user->id)
                  ->where('to_parent_id', $friend->id);
            })
            ->orWhere(function ($q) use ($user, $friend) {
                $q->where('from_parent_id', $friend->id)
                  ->where('to_parent_id', $user->id);
            })
            ->where('status', 'accepted')
            ->first();

        if (!$friendship) {
            return response()->json([
                'success' => false,
                'message' => 'Friend not found.'
            ], 404);
        }

        // Delete friendship
        FriendRequest::where(function ($q) use ($user, $friend) {
                $q->where('from_parent_id', $user->id)
                  ->where('to_parent_id', $friend->id);
            })
            ->orWhere(function ($q) use ($user, $friend) {
                $q->where('from_parent_id', $friend->id)
                  ->where('to_parent_id', $user->id);
            })
            ->delete();

        // Update history (optional)
        V2FriendRequestLog::where(function ($q) use ($user, $friend) {
                $q->where('from_parent_id', $user->id)
                  ->where('to_parent_id', $friend->id);
            })
            ->orWhere(function ($q) use ($user, $friend) {
                $q->where('from_parent_id', $friend->id)
                  ->where('to_parent_id', $user->id);
            })
            ->update([
                'status' => 'unfriended',
                'responded_at' => now(),
            ]);

        // Notification
        $notification = Notification::create([
            'user_id'       => $friend->id,
            'sender_id'     => $user->id,
            'notifiable_id' => $user->id,
            'type'          => 'friend_removed',
            'title'         => 'Friend Removed',
            'message'       => $user->first_name . ' removed you from friends.',
            'is_read'       => false,
        ]);

        // Push Notification
        if (!empty($friend->device_token)) {

            try {

                $this->fcm->sendNotification(
                    [$friend->device_token],
                    [
                        'title' => 'Friend Removed',
                        'body'  => $user->first_name . ' removed you from friends.',

                        'type' => 'friend_removed',
                        'notification_id' => (string)$notification->id,
                        'sender_id' => (string)$user->id,
                    ]
                );

            } catch (\Exception $e) {

                Log::error(
                    'Unfriend Notification Error: ' .
                    $e->getMessage()
                );
            }
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Friend removed successfully.'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        Log::error(
            'Unfriend Error: ' .
            $e->getMessage()
        );

        return response()->json([
            'success' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    /**
     * Format sent request response.
     *
     * @param V2FriendRequestLog $request
     * @return array
     */
    protected function formatSentRequestResponse(V2FriendRequestLog $request): array
    {
        return [
            'id' => $request->id,
            'receiver' => $request->receiver ? [
                'id' => $request->receiver->id,
                'name' => $request->receiver->first_name . ' ' . $request->receiver->last_name,
                'username' => $request->receiver->username,
                'profile' => $request->receiver->profile,
            ] : null,
            'status' => $request->status,
            'message' => $request->message,
            'created_at' => $request->created_at,
            'responded_at' => $request->responded_at,
        ];
    }

    /**
     * Format received request response.
     *
     * @param V2FriendRequestLog $request
     * @return array
     */
    protected function formatReceivedRequestResponse(V2FriendRequestLog $request): array
    {
        return [
            'id' => $request->id,
            'sender' => $request->sender ? [
                'id' => $request->sender->id,
                'name' => $request->sender->first_name . ' ' . $request->sender->last_name,
                'username' => $request->sender->username,
                'profile' => $request->sender->profile,
            ] : null,
            'status' => $request->status,
            'message' => $request->message,
            'created_at' => $request->created_at,
            'responded_at' => $request->responded_at,
        ];
    }
}
