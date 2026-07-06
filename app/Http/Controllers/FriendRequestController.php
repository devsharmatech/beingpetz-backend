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
public function latestReceivedRequests(Request $request)
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

    // latest 5 received requests only
    $requests = FriendRequest::with('fromParent', 'toParent')
        ->where('to_parent_id', $parentId)
        ->where('status', 'pending')
        ->latest()
        ->take(5)
        ->get();

    return response()->json([
        'status' => true,
        'data' => $requests,
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

   public function searchUsers_Old(Request $request)
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
        ->whereNotNull('first_name')
        ->where('deleted_at', 1);
    if ($search) {
    $suggestedPetsQuery->where(function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('username', 'like', "%{$search}%");
        //   ->orWhere('email', 'like', "%{$search}%");
    });
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

public function searchUsers(Request $request)
{
    $v = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
        'search'    => 'nullable|string|max:255',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $v->errors()->first(),
        ], 200);
    }

    $parentId = $request->parent_id;
    $search   = $request->search;

    $query = User::where('id', '!=', $parentId)
        ->whereNotNull('first_name')
        ->where('deleted_at', 1);

    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
        });
    }

    $users = $query->orderBy('first_name')->get();

    $users->transform(function ($user) use ($parentId) {

        $friendRequest = FriendRequest::where(function ($q) use ($parentId, $user) {
                $q->where('from_parent_id', $parentId)
                    ->where('to_parent_id', $user->id);
            })
            ->orWhere(function ($q) use ($parentId, $user) {
                $q->where('from_parent_id', $user->id)
                    ->where('to_parent_id', $parentId);
            })
            ->first();

        $user->is_friend = false;
        $user->request_sent = false;
        $user->request_received = false;
        $user->friend_request_status = null;

        if ($friendRequest) {

            $user->friend_request_status = $friendRequest->status;

            if ($friendRequest->status == 'accepted') {
                $user->is_friend = true;
            } elseif (
                $friendRequest->status == 'pending' &&
                $friendRequest->from_parent_id == $parentId
            ) {
                $user->request_sent = true;
            } elseif (
                $friendRequest->status == 'pending' &&
                $friendRequest->to_parent_id == $parentId
            ) {
                $user->request_received = true;
            }
        }

        return $user;
    });

    return response()->json([
        'status'  => true,
        'message' => 'Users fetched successfully.',
        'data'    => $users,
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
public function userFriendList(Request $request)
{
   try {
        $v = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => false,
                'message' => $v->errors()->first(),
            ], 422);
        }

        $userId = $request->user_id;
        $perPage = $request->get('per_page', 10);

        $friendRequests = FriendRequest::where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('from_parent_id', $userId)
                      ->orWhere('to_parent_id', $userId);
            })
            ->with([
                'fromParent:id,first_name,last_name,username,profile',
                'toParent:id,first_name,last_name,username,profile'
            ])
            ->latest()
            ->paginate($perPage);

        $friends = $friendRequests->getCollection()
            ->map(function ($friendRequest) use ($userId) {
                return $friendRequest->from_parent_id == $userId
                    ? $friendRequest->toParent
                    : $friendRequest->fromParent;
            })
            ->filter()
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'User friends fetched successfully.',
            'data' => $friends,
            'pagination' => [
                'current_page' => $friendRequests->currentPage(),
                'per_page' => $friendRequests->perPage(),
                'total' => $friendRequests->total(),
                'last_page' => $friendRequests->lastPage(),
                'has_more_pages' => $friendRequests->hasMorePages(),
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch user friends.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function showUserProfile(Request $request, $userId)
{
    try {
        $authUser = $request->user();

        $user = User::select(
                'id',
                'first_name',
                'last_name',
                'username',
                'email',
                'profile',
                'created_at'
            )
            ->find($userId);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.',
            ], 404);
        }

        $pets = Pet::where('user_id', $userId)
            ->select(
                'id',
                'user_id',
                'name',
                'type',
                'breed',
                'gender',
                'age',
                'bio',
                'avatar',
                'unid',
                'pet_unique_id',
                'created_at'
            )
            ->latest()
            ->get();

        $friendRequest = FriendRequest::where(function ($q) use ($authUser, $userId) {
                $q->where('from_parent_id', $authUser->id)
                  ->where('to_parent_id', $userId);
            })
            ->orWhere(function ($q) use ($authUser, $userId) {
                $q->where('from_parent_id', $userId)
                  ->where('to_parent_id', $authUser->id);
            })
            ->latest()
            ->first();

        $isFriend = false;
        $requestSentByMe = false;
        $requestReceivedByMe = false;
        $canSendRequest = true;
        $canCancelRequest = false;
        $canAcceptRequest = false;

        if ($friendRequest) {
            if ($friendRequest->status === 'accepted') {
                $isFriend = true;
                $canSendRequest = false;
            }

            if ($friendRequest->status === 'pending') {
                $canSendRequest = false;

                if ($friendRequest->from_parent_id == $authUser->id) {
                    $requestSentByMe = true;
                    $canCancelRequest = true;
                }

                if ($friendRequest->to_parent_id == $authUser->id) {
                    $requestReceivedByMe = true;
                    $canAcceptRequest = true;
                }
            }
        }

        if ($authUser->id == $userId) {
            $canSendRequest = false;
            $canCancelRequest = false;
            $canAcceptRequest = false;
        }

        return response()->json([
            'status' => true,
            'message' => 'User profile fetched successfully.',
            'data' => [
                'user' => $user,
                'pets' => $pets,
                'friendship' => [
                    'is_friend' => $isFriend,
                    'request_sent_by_me' => $requestSentByMe,
                    'request_received_by_me' => $requestReceivedByMe,
                    'friend_request_status' => $friendRequest->status ?? null,
                    'friend_request_id' => $friendRequest->id ?? null,
                    'can_send_request' => $canSendRequest,
                    'can_cancel_request' => $canCancelRequest,
                    'can_accept_request' => $canAcceptRequest,
                ]
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch user profile.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getAllUsers(Request $request)
{
    $validator = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $users = User::select([
            'id',
            'user_id',
            'username',
            'first_name',
            'last_name',
            'profile',
            'city',
            'state',
            'email'
        ])
        ->where('id', '!=', $request->parent_id)
        ->whereNotNull('first_name')
        ->where('deleted_at', 1)
        ->orderBy('first_name')
        ->paginate(50);

    $users->getCollection()->transform(function ($user) {

        return [
            'id' => $user->id,
            'user_id' => $user->user_id,
            'username' => $user->username,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => trim($user->first_name . ' ' . $user->last_name),
            'profile' => $user->profile ? url($user->profile) : null,
            'city' => $user->city,
            'state' => $user->state,
            'email' => $user->email,
        ];
    });

    return response()->json([
        'status' => true,
        'message' => 'Users fetched successfully.',
        'data' => $users,
    ]);
}

}
