<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Models\Notification;
use App\Services\FirebaseService;


class FriendRequestController extends Controller
{
     protected $fcm;

    public function __construct()
    {
        $this->fcm = new FirebaseService(env('FIREBASE_PROJECT_ID'),public_path(env('FIREBASE_CREDENTIALS_PATH')));
    }
    
    public function sendRequestOld(Request $request)
    {
        $v = Validator::make($request->all(), [
            'from_parent_id' => 'required|exists:users,id',
            'to_parent_id'   => 'required|exists:users,id|different:from_parent_id',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => false,
                'message' => $v->errors()->first(),
            ], 422);
        }

        // Check if already sent
        $exists = FriendRequest::where(function($q) use ($request) {
            $q->where('from_parent_id', $request->from_parent_id)
              ->where('to_parent_id', $request->to_parent_id);
        })->orWhere(function($q) use ($request) {
            $q->where('from_parent_id', $request->to_parent_id)
              ->where('to_parent_id', $request->from_parent_id);
        })->first();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Friend request already exists.',
            ], 200);
        }

        $friendRequest = FriendRequest::create([
            'from_parent_id' => $request->from_parent_id,
            'to_parent_id'   => $request->to_parent_id,
            'status'      => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Friend request sent.',
            'data' => $friendRequest,
        ], 201);
    }
    
    public function sendRequest(Request $request)
   {
    $v = Validator::make($request->all(), [
        'from_parent_id' => 'required|exists:users,id',
        'to_parent_id'   => 'required|exists:users,id|different:from_parent_id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
        ], 422);
    }

    // Check if already sent
    $exists = FriendRequest::where(function($q) use ($request) {
        $q->where('from_parent_id', $request->from_parent_id)
          ->where('to_parent_id', $request->to_parent_id);
    })->orWhere(function($q) use ($request) {
        $q->where('from_parent_id', $request->to_parent_id)
          ->where('to_parent_id', $request->from_parent_id);
    })->first();

    if ($exists) {
        return response()->json([
            'status' => false,
            'message' => 'Friend request already exists.',
        ], 200);
    }

    $friendRequest = FriendRequest::create([
        'from_parent_id' => $request->from_parent_id,
        'to_parent_id'   => $request->to_parent_id,
        'status'      => 'pending',
    ]);

    // Send notification to the recipient of the friend request
    $sender = User::find($request->from_parent_id);
    $recipient = User::find($request->to_parent_id);
    
    // Create notification in database
    $notification = Notification::create([
        'user_id'       => $request->to_parent_id,
        'sender_id'     => $request->from_parent_id,
        'notifiable_id' => $friendRequest->id,
        'type'          => 'friend_request',
        'title'         => 'New friend request',
        'message'       => $sender->first_name . ' sent you a friend request',
        'is_read'       => false,
    ]);

    // Send push notification to recipient
    if ($recipient && $recipient->device_token) {
        try {
            $this->fcm->sendNotification(
                [$recipient->device_token],
                [
                    'title' => 'New friend request',
                    'body'  => $sender->first_name . ' sent you a friend request',
                    'sender_id' => (string) $request->from_parent_id,
                    'type' => 'friend_request',
                    'notification_id' => (string) $notification->id,
                    'notifiable_id' => (string) $friendRequest->id
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Friend request notification failed: " . $e->getMessage());
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Friend request sent.',
        'data' => $friendRequest,
    ], 201);
}
    
    public function respondRequestOld(Request $request)
    {
        $v = Validator::make($request->all(), [
            'request_id' => 'required|exists:friend_requests,id',
            'action'     => 'required|in:accepted,rejected',
            'parent_id'     => 'required|exists:users,id',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => false,
                'message' => $v->errors()->first(),
            ], 422);
        }

        $friendRequest = FriendRequest::find($request->request_id);

        if (!$friendRequest || $friendRequest->to_parent_id != $request->parent_id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized or invalid request.',
            ], 403);
        }

        $friendRequest->status = $request->action;
        $friendRequest->save();

        return response()->json([
            'status' => true,
            'message' => "Friend request " . $request->action . ".",
            'data' => $friendRequest,
        ], 200);
    }
    
    public function respondRequest(Request $request)
    {
    $v = Validator::make($request->all(), [
        'request_id' => 'required|exists:friend_requests,id',
        'action'     => 'required|in:accepted,rejected',
        'parent_id'     => 'required|exists:users,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
        ], 422);
    }

    $friendRequest = FriendRequest::find($request->request_id);

    if (!$friendRequest || $friendRequest->to_parent_id != $request->parent_id) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized or invalid request.',
        ], 403);
    }

    $friendRequest->status = $request->action;
    $friendRequest->save();

    // Send notification to the original sender about the response
    $responder = User::find($request->parent_id);
    $originalSender = User::find($friendRequest->from_parent_id);
    
    $actionMessage = $request->action == 'accepted' 
        ? 'accepted your friend request' 
        : 'declined your friend request';
    
    // Create notification in database
    $notification = Notification::create([
        'user_id'       => $friendRequest->from_parent_id,
        'sender_id'     => $request->parent_id,
        'notifiable_id' => $friendRequest->id,
        'type'          => 'friend_request_response',
        'title'         => 'Friend request ' . $request->action,
        'message'       => $responder->first_name . ' ' . $actionMessage,
        'is_read'       => false,
    ]);

    // Send push notification to original sender
    if ($originalSender && $originalSender->device_token) {
        try {
            $this->fcm->sendNotification(
                [$originalSender->device_token],
                [
                    'title' => 'Friend request ' . $request->action,
                    'body'  => $responder->first_name . ' ' . $actionMessage,
                    'sender_id' => (string) $request->parent_id,
                    'type' => 'friend_request_response',
                    'notification_id' => (string) $notification->id,
                    'notifiable_id' => (string) $friendRequest->id
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Friend request response notification failed: " . $e->getMessage());
        }
    }

    return response()->json([
        'status' => true,
        'message' => "Friend request " . $request->action . ".",
        'data' => $friendRequest,
    ], 200);
}
    
    public function getRequests(Request $request)
    {
        $v = Validator::make($request->all(), [
            'parent_id' => 'required|exists:users,id',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => false,
                'message' => $v->errors()->first(),
            ], 422);
        }

        $parentId = $request->parent_id;

        $sentRequests = FriendRequest::where('from_parent_id', $parentId)->with('fromParent','toParent')
                        ->where('status', 'pending')
                        ->orderByDesc('id')->get();
        $receivedRequests = FriendRequest::where('to_parent_id', $parentId)->with('fromParent','toParent')
                           ->where('status', 'pending')
                           ->orderByDesc('id')->get();

        return response()->json([
            'status' => true,
            'sent_requests' => $sentRequests,
            'received_requests' => $receivedRequests,
        ], 200);
    }

    
    public function friendSuggestions(Request $request)
    {
    $v = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
        ], 200);
    }

    $parentId = $request->parent_id;

    
    $relatedPetIds = FriendRequest::where('from_parent_id', $parentId)
        ->orWhere('to_parent_id', $parentId)
        ->pluck('from_parent_id')
        ->merge(
            FriendRequest::where('from_parent_id', $parentId)
                ->orWhere('to_parent_id', $parentId)
                ->pluck('to_parent_id')
        )
        ->unique()
        ->push($parentId); 
     $suggestedPets = User::whereNotIn('id', $relatedPetIds)->where('first_name','!=',null)
        ->inRandomOrder()
        ->limit(10)
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Suggested friends fetched successfully.',
        'data' => $suggestedPets,
    ]);
}

   public function searchUsers(Request $request)
{
    $v = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
        'search' => 'nullable|string|max:255',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
        ], 200);
    }

    $parentId = $request->parent_id;
    $search = $request->search;

    $relatedPetIds = FriendRequest::where('from_parent_id', $parentId)
        ->orWhere('to_parent_id', $parentId)
        ->pluck('from_parent_id')
        ->merge(
            FriendRequest::where('from_parent_id', $parentId)
                ->orWhere('to_parent_id', $parentId)
                ->pluck('to_parent_id')
        )
        ->unique()
        ->push($parentId);

    $suggestedPetsQuery = User::whereNotIn('id', $relatedPetIds)
        ->whereNotNull('first_name');
    if ($search) {
        $suggestedPetsQuery->where('first_name', 'like', '%' . $search . '%');
    }

    $suggestedPets = $suggestedPetsQuery
        ->inRandomOrder()
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Suggested friends fetched successfully.',
        'data' => $suggestedPets,
    ]);
}

public function getFriends(Request $request)
{
    $v = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status' => false,
            'message' => $v->errors()->first(),
        ], 422);
    }

    $parentId = $request->parent_id;

    // Get accepted friend requests where the user is either sender or receiver
    $friends = FriendRequest::where(function($query) use ($parentId) {
            $query->where('from_parent_id', $parentId)
                  ->orWhere('to_parent_id', $parentId);
        })
        ->where('status', 'accepted')
        ->with(['fromParent', 'toParent'])
        ->get()
        ->map(function ($request) use ($parentId) {
            return $request->from_parent_id == $parentId ? $request->toParent : $request->fromParent;
        });

    return response()->json([
        'status' => true,
        'message' => 'Friend list fetched successfully.',
        'data' => $friends,
    ]);
}

}
