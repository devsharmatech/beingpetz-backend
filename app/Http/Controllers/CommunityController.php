<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Community;
use App\Models\CommunityMembership;
use App\Models\CommunityMessage;
use App\Models\CommunityModerator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CommunitiesExport;

class CommunityController extends Controller
{
protected $fcm;
private $maxCommunityMembers = 100;

 public function __construct()
    {
        $this->fcm = new FirebaseService(env('FIREBASE_PROJECT_ID'),public_path(env('FIREBASE_CREDENTIALS_PATH')));
    }


public function createCommunityWithMembers(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'         => 'required|string|unique:communities,name',
        'description'  => 'nullable|string',
        'type'         => 'nullable|in:public,private',
        'latitude'     => 'nullable|string',
        'longitude'    => 'nullable|string',
        'profile'      => 'nullable|image',
        'cover_image'  => 'nullable|image',
        'created_by'   => 'required|exists:users,id',
        'members'      => 'nullable|array',
        'members.*'    => 'exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422);
    }

    DB::beginTransaction();

    try {

        $community = new Community();
        $community->name = $request->name;
        $community->slug = Str::slug($request->name) . '-' . Str::random(5);
        $community->description = $request->description;
        $community->type = $request->type ?? 'private';
        $community->latitude = $request->latitude;
        $community->longitude = $request->longitude;
        $community->created_by = $request->created_by;

        $manager = new ImageManager(new Driver());

        // Profile Image
        if ($request->hasFile('profile')) {

            $file = $request->file('profile');

            $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();

            $path = 'uploads/communities/' . $filename;

            $manager->read($file)
                ->resize(400, 400)
                ->save(public_path($path));

            $community->profile = $path;
        }

        // Cover Image
        if ($request->hasFile('cover_image')) {

            $file = $request->file('cover_image');

            $filename = uniqid('cover_') . '.' . $file->getClientOriginalExtension();

            $path = 'uploads/communities/' . $filename;

            $manager->read($file)
                ->resize(1200, 400)
                ->save(public_path($path));

            $community->cover_image = $path;
        }

        $community->save();

        // Creator becomes Super Admin
        CommunityMembership::create([
            'community_id' => $community->id,
            'parent_id'    => $community->created_by,
            'role'         => 'super_admin',
            'status'       => 1,
        ]);

        $creator = User::find($community->created_by);

        $memberIds = collect($request->members ?? [])
            ->unique()
            ->filter()
            ->reject(function ($id) use ($community) {
                return $id == $community->created_by;
            });
        if ($memberIds->count() > 99) {
    return response()->json([
        'status' => false,
        'message' => 'A community can have a maximum of 100 members including the creator.'
    ], 422);
}

        foreach ($memberIds as $memberId) {

            $exists = CommunityMembership::where('community_id', $community->id)
                ->where('parent_id', $memberId)
                ->exists();

            if ($exists) {
                continue;
            }

            CommunityMembership::create([
                'community_id' => $community->id,
                'parent_id'    => $memberId,
                'role'         => 'member',
                'status'       => 1,
            ]);

            $member = User::find($memberId);

            // Create Notification Record
            $notification = Notification::create([
                'user_id'       => $memberId,
                'sender_id'     => $community->created_by,
                'notifiable_id' => $community->id,
                'type'          => 'community_added',
                'title'         => 'Added To Community',
                'message'       => $creator->first_name . ' added you to ' . $community->name,
                'is_read'       => false,
            ]);

            // Send Push Notification
            if ($member && !empty($member->device_token)) {

                try {

                    $this->fcm->sendNotification(
                        [$member->device_token],
                        [
                            'title' => 'Added To Community',
                            'body' => $creator->first_name . ' added you to ' . $community->name,
                            'type' => 'community_added',
                            'community_id' => (string) $community->id,
                            'notification_id' => (string) $notification->id,
                            'sender_id' => (string) $community->created_by,
                        ]
                    );

                } catch (\Exception $e) {

                    \Log::error(
                        'Community notification failed: ' .
                        $e->getMessage()
                    );
                }
            }
        }

        DB::commit();

        $community = Community::with([
            'creator',
            'members.user'
        ])->find($community->id);

        return response()->json([
            'status'  => true,
            'message' => 'Community created successfully.',
            'data'    => $community,
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error(
            'Create Community Error: ' .
            $e->getMessage()
        );

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function createCommunity(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|unique:communities,name',
        'description' => 'nullable|string',
        'type' => 'nullable|string', 
        'latitude' => 'nullable|string',
        'longitude' => 'nullable|string',
        'profile' => 'nullable|image',
        'cover_image' => 'nullable|image',
        'created_by' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422);
    }

    $data = $validator->validated();
    $community = new Community();
    $community->name = $data['name'];
    $community->slug = Str::slug($data['name']) . '-' . Str::random(5);
    $community->description = $data['description'] ?? null;
    $community->type = $data['type'] ?? 'public';
    $community->latitude = $data['latitude'] ?? null;
    $community->longitude = $data['longitude'] ?? null;
    $community->created_by = $data['created_by'];

    $manager = new ImageManager(new Driver());

    if ($request->hasFile('profile')) {
        $file = $request->file('profile');
        $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/communities/' . $filename;
        $manager->read($file)->resize(400, 400)->save(public_path($path));
        $community->profile = $path;
    }

    if ($request->hasFile('cover_image')) {
        $file = $request->file('cover_image');
        $filename = uniqid('cover_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/communities/' . $filename;
        $manager->read($file)->resize(1200, 400)->save(public_path($path));
        $community->cover_image = $path;
    }

    $community->save();
    $superAdmin=new CommunityMembership();
    $superAdmin->community_id=$community->id;
    $superAdmin->parent_id=$community->created_by;
    $superAdmin->role='super_admin';
    $superAdmin->status=1;
    $superAdmin->save();
    
    return response()->json([
        'status' => true,
        'message' => 'Community created successfully!',
        'data' => $community,
    ]);
}

public function getCommunity(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422);
    }
    $community = Community::where('id',$request->community_id)->with('creator','members.user')->first();
    return response()->json([
        'status' => true,
        'data' => $community,
    ]);
}

public function getCommunities_old(Request $request)
{
    $communities = Community::with('creator','users','superAdmin','admins','members')->latest()->get();
    return response()->json([
        'status' => true,
        'data' => $communities,
    ]);
}
public function getCommunities(Request $request)
{
    $userId = $request->user_id;

    $query = Community::with([
        'creator',
        'users',
        'superAdmin',
        'admins',
        'members',
    ]);

    // Only check membership if user_id is provided
    if ($userId) {
        $query->addSelect([
            'join_status' => CommunityMembership::select('status')
                ->whereColumn('community_id', 'communities.id')
                ->where('parent_id', $userId)
                ->limit(1)
        ]);
    }

    $communities = $query->latest()->get();

    $communities->transform(function ($community) use ($userId) {

        // If no user_id passed, default to not_joined
        if (!$userId) {
            $community->isJoined = 'not_joined';
            return $community;
        }

        if (is_null($community->join_status)) {
            $community->isJoined = 'not_joined';
        } elseif ((int) $community->join_status === 0) {
            $community->isJoined = 'pending';
        } else {
            $community->isJoined = 'joined';
        }

        unset($community->join_status);

        return $community;
    });

    return response()->json([
        'status' => true,
        'data' => $communities,
    ]);
}
public function searchCommunity(Request $request)
{
    
    if ($request->has('search') && !empty($request->search)) {
     $query = Community::with('creator')->latest();
        $searchTerm = $request->search;
        $query->where('name', 'LIKE', '%' . $searchTerm . '%');
        $communities = $query->get();
    }else{
        $communities=collect();
    }
    return response()->json([
        'status' => true,
        'data' => $communities,
    ]);
}

public function joinCommunity_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ]);
    }

    // Check if already joined
    $exists = CommunityMembership::where('community_id', $request->community_id)
        ->where('parent_id', $request->parent_id)
        
        ->exists();

    if ($exists) {
        $messageRes=$exists->status=1?"Already joined this community!":"Already requested to join this community!";
        return response()->json([
            'status' => false,
            'message' => $messageRes,
        ]);
    }
    $community = Community::find($request->community_id);

    $status = $community->type === 'private'
    ? '0'
    : '1';
    
    CommunityMembership::create([
        'community_id' => $request->community_id,
        'parent_id'    => $request->parent_id,
        'role'         => 'member',
        'status'       => $status,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Joined successfully!',
    ]);
}

public function joinCommunity(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id'    => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    DB::beginTransaction();

    try {
        
        
        
        $community = Community::find($request->community_id);
        
        $currentMembers = CommunityMembership::where('community_id', $community->id)
                         ->where('status', 1)
                         ->count();

        if ($currentMembers >= $this->maxCommunityMembers) {
    return response()->json([
        'status' => false,
        'message' => 'This community has reached its maximum limit of 100 members.'
    ], 422);
}
        
        $user = User::find($request->parent_id);

        // Already joined/requested?
        $exists = CommunityMembership::where('community_id', $community->id)
            ->where('parent_id', $user->id)
            ->first();

        if ($exists) {

            if ($exists->status == 1) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You are already a member of this community.',
                ]);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Your join request is already pending.',
            ]);
        }

        // Public = Direct Join
        if ($community->type == 'public') {

            CommunityMembership::create([
                'community_id' => $community->id,
                'parent_id'    => $user->id,
                'role'         => 'member',
                'status'       => 1,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Joined community successfully.',
            ]);
        }

        // Private Community -> Pending Request
        CommunityMembership::create([
            'community_id' => $community->id,
            'parent_id'    => $user->id,
            'role'         => 'member',
            'status'       => 0,
        ]);

        // Notify Admin & Super Admin
        $admins = CommunityMembership::with('user')
            ->where('community_id', $community->id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('status', 1)
            ->get();

        foreach ($admins as $admin) {

            $notification = Notification::create([
                'user_id'       => $admin->parent_id,
                'sender_id'     => $user->id,
                'notifiable_id' => $community->id,
                'type'          => 'community_join_request',
                'title'         => 'New Join Request',
                'message'       => $user->first_name . ' requested to join ' . $community->name,
                'is_read'       => false,
            ]);

            if ($admin->user && !empty($admin->user->device_token)) {

                try {

                    $this->fcm->sendNotification(
                        [$admin->user->device_token],
                        [
                            'title' => 'New Join Request',
                            'body'  => $user->first_name . ' requested to join ' . $community->name,

                            'type' => 'community_join_request',

                            'community_id' => (string)$community->id,
                            'notification_id' => (string)$notification->id,

                            // Requested user
                            'request_user_id' => (string)$user->id,
                            'request_user_name' => $user->first_name . ' ' . $user->last_name,

                            // UI Action
                            'action_required' => 'true',
                        ]
                    );

                } catch (\Exception $e) {

                    \Log::error(
                        'Community Join Request Notification Error : '
                        . $e->getMessage()
                    );
                }
            }
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Join request sent successfully. Waiting for admin approval.',
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error(
            'Join Community Error : '
            . $e->getMessage()
        );

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function addModeratorOrAdmin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id' => 'required|exists:users,id',
        'role' => 'required|in:admin,moderator',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ]);
    }

    CommunityMembership::updateOrCreate(
        [
            'community_id' => $request->community_id,
            'parent_id' => $request->parent_id,
        ],
        [
            'role' => $request->role,
        ]
    );

    return response()->json([
        'status' => true,
        'message' => ucfirst($request->role) . ' added successfully!',
    ]);
}

public function removeModeratorOrAdmin(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ]);
    }

    CommunityMembership::where('community_id', $request->community_id)
        ->where('parent_id', $request->parent_id)
        ->delete();

    return response()->json([
        'status' => true,
        'message' => 'User role removed from community.',
    ]);
}


public function leftCommunityOld(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ]);
    }

    // Check if already joined
    $exist = CommunityMembership::where('community_id', $request->community_id)
        ->where('parent_id', $request->user_id)
        ->first();

    if (isset($exist->id)) {
        $exist->delete();
        CommunityMessage::where('community_id',$request->community_id)->where('parent_id', $request->user_id)->delete();
        return response()->json([
            'status' => true,
            'message' => 'You left from this community!',
        ]);
    }
    return response()->json([
        'status' => false,
        'message' => 'This Community member not found!',
    ]);
}

public function leftCommunity(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ]);
    }

    $community = Community::find($request->community_id);

    // Find membership
    $exist = CommunityMembership::where('community_id', $community->id)
        ->where('parent_id', $request->user_id)
        ->first();

    if (!$exist) {
        return response()->json([
            'status' => false,
            'message' => 'This Community member not found!',
        ]);
    }

    // Delete membership + user messages
    $exist->delete();
    CommunityMessage::where('community_id', $community->id)
        ->where('parent_id', $request->user_id)
        ->delete();

    // Check remaining members
    $remainingMembers = CommunityMembership::where('community_id', $community->id)->get();
    $count = $remainingMembers->count();
    // dd($count);
    if ($count === 0) {
        // If no members left → delete the community
        $community->delete();

    } elseif ($count === 1) {
        // If one member left → ensure super_admin
        $member = $remainingMembers->first();
        if ($member->role !== 'super_admin') {
            $member->update(['role' => 'super_admin']);
        }
    } else {
        // More than one member left → make sure at least one super_admin exists
        $hasSuperAdmin = $remainingMembers->contains(fn($m) => $m->role == 'super_admin');
        if (!$hasSuperAdmin) {
            // Promote the earliest joined member
            $firstMember = $remainingMembers->sortBy('created_at')->first();
            $firstMember->update(['role' => 'super_admin']);
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'You left from this community!',
    ]);
}


public function myJoinedCommunities(Request $request)
{
    $validator = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ]);
    }
    $joined = CommunityMembership::with('community.creator')
        ->where('parent_id', $request->parent_id)
        ->where('status',1)
        ->paginate(10);
    return response()->json([
        'status' => true,
        'data' => $joined,
    ]);
   }


    public function updateCommunityProfile_old(Request $request)
    {
    $community_id=$request->community_id ?? null;
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'name' => 'nullable|string|unique:communities,name,' . $community_id,
        'profile' => 'nullable|image',
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422);
    }

    $community = Community::find($community_id);

    if (!$community) {
        return response()->json([
            'status' => false,
            'message' => 'Community not found.',
        ], 404);
    }

    // Check if user is admin or super_admin
    $isAdmin = CommunityMembership::where('community_id', $community_id)
                ->where('parent_id', $request->user_id)
                ->whereIn('role', ['admin', 'super_admin'])
                ->exists();

    if (!$isAdmin) {
        return response()->json([
            'status' => false,
            'message' => 'You are not authorized to update this community.',
        ], 403);
    }

    if ($request->filled('name')) {
        $community->name = $request->name;
        $community->slug = Str::slug($request->name) . '-' . Str::random(5); // Optional: regenerate slug
    }

    if ($request->hasFile('profile')) {
        $file = $request->file('profile');
        $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/communities/' . $filename;

        $manager = new ImageManager(new Driver());
        $manager->read($file)->resize(400, 400)->save(public_path($path));

        $community->profile = $path;
    }

    $community->save();

    return response()->json([
        'status' => true,
        'message' => 'Community updated successfully.',
        'data' => $community,
    ]);
}

public function updateCommunityProfile(Request $request)
{
    $community_id = $request->community_id;

    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'user_id' => 'required|exists:users,id',
        'name' => 'required|string|max:255|unique:communities,name,' . $community_id,
        'description' => 'nullable|string',
        'type' => 'required|in:public,private',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'members' => 'nullable|array',
        'members.*' => 'exists:users,id',
        'moderators' => 'nullable|array',
        'moderators.*' => 'exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422);
    }

    $community = Community::find($community_id);

    if (!$community) {
        return response()->json([
            'status' => false,
            'message' => 'Community not found.',
        ], 404);
    }

    // Check if user is admin or super_admin
    $isAdmin = CommunityMembership::where('community_id', $community_id)
        ->where('parent_id', $request->user_id)
        ->whereIn('role', ['admin', 'super_admin'])
        ->exists();

    if (!$isAdmin) {
        return response()->json([
            'status' => false,
            'message' => 'You are not authorized to update this community.',
        ], 403);
    }

    // Update slug only if name changed
    if ($community->name !== $request->name) {
        $community->slug = Str::slug($request->name) . '-' . Str::random(5);
    }

    // Update basic details
    $community->fill([
        'name' => $request->name,
        'description' => $request->description,
        'type' => $request->type,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
    ]);

    $manager = new ImageManager(new Driver());

    // Profile Image
    if ($request->hasFile('profile')) {

        $oldProfile = $community->getRawOriginal('profile');

        if ($oldProfile && file_exists(public_path($oldProfile))) {
            unlink(public_path($oldProfile));
        }

        $file = $request->file('profile');
        $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/communities/' . $filename;

        $manager->read($file)
            ->resize(400, 400)
            ->save(public_path($path));

        $community->profile = $path;
    }

    // Cover Image
    if ($request->hasFile('cover_image')) {

        $oldCover = $community->getRawOriginal('cover_image');

        if ($oldCover && file_exists(public_path($oldCover))) {
            unlink(public_path($oldCover));
        }

        $file = $request->file('cover_image');
        $filename = uniqid('cover_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/communities/' . $filename;

        $manager->read($file)
            ->resize(1200, 400)
            ->save(public_path($path));

        $community->cover_image = $path;
    }

    $community->save();

    // ==========================
    // Update Members
    // ==========================

    $selectedMembers = $request->members ?? [];

    $currentMembers = CommunityMembership::where('community_id', $community->id)
        ->where('role', '!=', 'super_admin')
        ->pluck('parent_id')
        ->toArray();

    $membersToRemove = array_diff($currentMembers, $selectedMembers);
    $membersToAdd = array_diff($selectedMembers, $currentMembers);

    CommunityMembership::where('community_id', $community->id)
        ->whereIn('parent_id', $membersToRemove)
        ->where('role', '!=', 'super_admin')
        ->delete();

    foreach ($membersToAdd as $memberId) {
        CommunityMembership::create([
            'community_id' => $community->id,
            'parent_id' => $memberId,
            'role' => 'member',
        ]);
    }

    // ==========================
    // Update Moderators
    // ==========================

    $selectedModerators = $request->moderators ?? [];

    $currentModerators = CommunityModerator::where('community_id', $community->id)
        ->pluck('user_id')
        ->toArray();

    $moderatorsToRemove = array_diff($currentModerators, $selectedModerators);
    $moderatorsToAdd = array_diff($selectedModerators, $currentModerators);

    CommunityModerator::where('community_id', $community->id)
        ->whereIn('user_id', $moderatorsToRemove)
        ->delete();

    foreach ($moderatorsToAdd as $moderatorId) {
        CommunityModerator::create([
            'community_id' => $community->id,
            'user_id' => $moderatorId,
            'role' => 'moderator',
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Community updated successfully.',
        'data' => $community->fresh(),
    ]);
}

    public function autoDeleteEmptyCommunities()
    {
    $tenMinutesAgo = now()->subMinutes(10);

    $communities = Community::where('created_at', '<=', $tenMinutesAgo)
        ->whereDoesntHave('memberships') 
        ->get();

    if ($communities->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No empty communities found to delete.',
        ]);
    }

    foreach ($communities as $community) {
        CommunityMessage::where('community_id', $community->id)->delete();
        $community->delete();
    }

    return response()->json([
        'status' => true,
        'message' => $communities->count() . ' empty communities deleted successfully.',
    ]);
}

    // admin panel functions start here
    public function index()
    {
        $communities = Community::with(['creator', 'members.user', 'moderators.user'])->latest()->get();
        return view('admin.community.index', compact('communities'));
    }

    public function create()
    {
        $users = User::all();
        return view('admin.community.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:communities,name',
            'description' => 'nullable|string',
            'type' => 'required|in:public,private',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'moderators' => 'nullable|array',
            'moderators.*' => 'exists:users,id'
        ]);

        // Create community
        $community = Community::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'description' => $request->description,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'created_by' => auth()->id(),
        ]);

        $manager = new ImageManager(new Driver());

        // Handle profile image
        if ($request->hasFile('profile')) {
            $file = $request->file('profile');
            $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/communities/' . $filename;

            $manager->read($file)->resize(400, 400)->save(public_path($path));
            $community->update(['profile' => $path]);
        }

        // Handle cover image
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = uniqid('cover_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/communities/' . $filename;

            $manager->read($file)->resize(1200, 400)->save(public_path($path));
            $community->update(['cover_image' => $path]);
        }

        // Add selected members
        if ($request->has('members')) {
            foreach ($request->members as $memberId) {
                CommunityMembership::create([
                    'community_id' => $community->id,
                    'parent_id' => $memberId,
                    'role' => 'member'
                ]);
            }
        }

        // Add moderators
        if ($request->has('moderators')) {
            foreach ($request->moderators as $moderatorId) {
                CommunityModerator::create([
                    'community_id' => $community->id,
                    'user_id' => $moderatorId,
                    'role' => 'moderator'
                ]);
            }
        }

        return redirect()->route('admin.community.index')->with('success', 'Community created successfully.');
    }

    public function edit(Community $community)
    {
        $users = User::all();
        $selectedMembers = $community->members->pluck('parent_id')->toArray();
        $selectedModerators = $community->moderators->pluck('user_id')->toArray();
        
        return view('admin.community.edit', compact('community', 'users', 'selectedMembers', 'selectedModerators'));
    }

    public function update(Request $request, Community $community)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:communities,name,' . $community->id,
            'description' => 'nullable|string',
            'type' => 'required|in:public,private',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
            'moderators' => 'nullable|array',
            'moderators.*' => 'exists:users,id'
        ]);

        // Only update slug if name changed
        if ($community->name !== $request->name) {
            $community->slug = Str::slug($request->name) . '-' . Str::random(5);
        }

        // Update community basic info
        $community->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        $manager = new ImageManager(new Driver());

        // Handle profile image update with resizing
    if ($request->hasFile('profile')) {
        $oldProfile = $community->getRawOriginal('profile');
        if ($oldProfile && file_exists(public_path($oldProfile))) {
            unlink(public_path($oldProfile));
        }

        $file = $request->file('profile');
        $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/communities/' . $filename;

        $manager->read($file)->resize(400, 400)->save(public_path($path));
        $community->profile = $path;
    }

    // Handle cover image update with resizing
    if ($request->hasFile('cover_image')) {
        $oldCover = $community->getRawOriginal('cover_image');
        if ($oldCover && file_exists(public_path($oldCover))) {
            unlink(public_path($oldCover));
        }

        $file = $request->file('cover_image');
        $filename = uniqid('cover_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/communities/' . $filename;

        $manager->read($file)->resize(1200, 400)->save(public_path($path));
        $community->cover_image = $path;
    }

    $community->save();


        // Handle members update
        $selectedMembers = $request->members ?? [];
        $currentMembers = CommunityMembership::where('community_id', $community->id)
            ->where('role', '!=', 'super_admin')
            ->pluck('parent_id')
            ->toArray();

        $membersToRemove = array_diff($currentMembers, $selectedMembers);
        $membersToAdd = array_diff($selectedMembers, $currentMembers);

        // Remove members
        CommunityMembership::where('community_id', $community->id)
            ->whereIn('parent_id', $membersToRemove)
            ->where('role', '!=', 'super_admin')
            ->delete();

        // Add new members
        foreach ($membersToAdd as $memberId) {
            CommunityMembership::create([
                'community_id' => $community->id,
                'parent_id' => $memberId,
                'role' => 'member'
            ]);
        }

        // Handle moderators update
        $selectedModerators = $request->moderators ?? [];
        $currentModerators = $community->moderators->pluck('user_id')->toArray();

        $moderatorsToRemove = array_diff($currentModerators, $selectedModerators);
        $moderatorsToAdd = array_diff($selectedModerators, $currentModerators);

        // Remove moderators
        CommunityModerator::where('community_id', $community->id)
            ->whereIn('user_id', $moderatorsToRemove)
            ->delete();

        // Add new moderators
        foreach ($moderatorsToAdd as $moderatorId) {
            CommunityModerator::create([
                'community_id' => $community->id,
                'user_id' => $moderatorId,
                'role' => 'moderator'
            ]);
        }

        return redirect()->route('admin.community.index')->with('success', 'Community updated successfully.');
    }

    public function destroy(Community $community)
    {
        // Delete related records
        CommunityModerator::where('community_id', $community->id)->delete();
        CommunityMembership::where('community_id', $community->id)->delete();
        CommunityMessage::where('community_id', $community->id)->delete();
        
        // Delete images
        if ($community->profile && file_exists(public_path($community->profile))) {
            unlink(public_path($community->profile));
        }
        if ($community->cover_image && file_exists(public_path($community->cover_image))) {
            unlink(public_path($community->cover_image));
        }
        
        $community->delete();
        
        return redirect()->route('admin.community.index')->with('success', 'Community deleted successfully.');
    }

    // Export functionality
    public function export()
    {
        return Excel::download(new CommunitiesExport, 'communities-' . date('Y-m-d') . '.xlsx');
    }

    // Show transfer ownership form
    public function showTransferForm($id)
    {
        $community = Community::with(['creator', 'moderators.user'])->findOrFail($id);
        $users = User::where('id', '!=', $community->created_by)->get();
        
        return view('admin.community.transfer-ownership', compact('community', 'users'));
    }

    // Transfer ownership
    public function transferOwnership(Request $request, $id)
    {
        $request->validate([
            'new_owner_id' => 'required|exists:users,id',
            'transfer_type' => 'required|in:admin,moderator'
        ]);

        $community = Community::findOrFail($id);

        if ($request->transfer_type === 'admin') {
            // Transfer community ownership
            $community->update([
                'created_by' => $request->new_owner_id
            ]);

            // Remove the new owner from moderators if exists
            CommunityModerator::where('community_id', $community->id)
                ->where('user_id', $request->new_owner_id)
                ->delete();

            // Ensure the new owner is a member
            $existingMembership = CommunityMembership::where('community_id', $community->id)
                ->where('parent_id', $request->new_owner_id)
                ->first();

            if (!$existingMembership) {
                CommunityMembership::create([
                    'community_id' => $community->id,
                    'parent_id' => $request->new_owner_id,
                    'role' => 'super_admin'
                ]);
            } else {
                $existingMembership->update(['role' => 'super_admin']);
            }

        } else {
            // Add/Update as moderator
            $existingModerator = CommunityModerator::where('community_id', $community->id)
                ->where('user_id', $request->new_owner_id)
                ->first();

            if ($existingModerator) {
                $existingModerator->update(['role' => 'moderator']);
            } else {
                CommunityModerator::create([
                    'community_id' => $community->id,
                    'user_id' => $request->new_owner_id,
                    'role' => 'moderator'
                ]);
            }
        }

        return redirect()->route('admin.community.index')
            ->with('success', 'Ownership/Moderator transferred successfully.');
    }


// new apis
public function removeCommunityMember(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id'    => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    $member = CommunityMembership::where('community_id', $request->community_id)
        ->where('parent_id', $request->parent_id)
        ->first();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'Member not found.'
        ]);
    }

    if ($member->role === 'super_admin') {
        return response()->json([
            'status' => false,
            'message' => 'Super admin cannot be removed.'
        ]);
    }

    $member->delete();

    return response()->json([
        'status' => true,
        'message' => 'Member removed successfully.'
    ]);
}

public function makeAdminCommunity_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id'    => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    $newAdmin = CommunityMembership::where('community_id', $request->community_id)
        ->where('parent_id', $request->parent_id)
        ->where('status', '1')
        ->first();

    if (!$newAdmin) {
        return response()->json([
            'status' => false,
            'message' => 'User is not an approved member.'
        ]);
    }

    CommunityMembership::where('community_id', $request->community_id)
        ->where('role', 'admin')
        ->update(['role' => 'member']);

    $newAdmin->update([
        'role' => 'admin'
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Admin rights transferred successfully.'
    ]);
}

public function makeAdminCommunity(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id'    => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first()
        ], 422);
    }

    DB::beginTransaction();

    try {

        $community = Community::find($request->community_id);

        // User must be an approved member
        $newAdmin = CommunityMembership::where('community_id', $request->community_id)
            ->where('parent_id', $request->parent_id)
            ->where('status', '1')
            ->first();

        if (!$newAdmin) {
            return response()->json([
                'status'  => false,
                'message' => 'User is not an approved member.'
            ]);
        }

        if ($newAdmin->role == 'super_admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Super Admin cannot be changed.'
            ]);
        }

        if ($newAdmin->role == 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'User is already an Admin.'
            ]);
        }

        // Remove existing admin
        CommunityMembership::where('community_id', $request->community_id)
            ->where('role', 'admin')
            ->update([
                'role' => 'member'
            ]);

        // Make new admin
        $newAdmin->update([
            'role' => 'admin'
        ]);

        $user = User::find($request->parent_id);

        // Create Notification
        $notification = Notification::create([
            'user_id'       => $user->id,
            'sender_id'     => $community->created_by,
            'notifiable_id' => $community->id,
            'type'          => 'community_admin_assigned',
            'title'         => 'Community Admin Assigned',
            'message'       => 'You have been assigned as an admin of the "' . $community->name . '" community.',
            'is_read'       => false,
        ]);

        // Send Push Notification
        if ($user && !empty($user->device_token)) {

            try {

                $this->fcm->sendNotification(
    [$user->device_token],
    [
        'title' => 'Community Admin Assigned',
        'body'  => 'You have been assigned as an admin of the "' . $community->name . '" community.',
        'type' => 'community_admin_assigned',
        'community_id' => (string) $community->id,
        'notification_id' => (string) $notification->id,
        'sender_id' => (string) $community->created_by,
    ]
);

            } catch (\Exception $e) {

                \Log::error(
                    'Community Admin Notification Error: ' .
                    $e->getMessage()
                );
            }
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Admin assigned successfully.'
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error(
            'Make Admin Error: ' .
            $e->getMessage()
        );

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage()
        ], 500);
    }
}

public function pendingCommunityMemberRequest_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    $requests = CommunityMembership::with('user')
        ->where('community_id', $request->community_id)
        ->where('status', '0')
        ->get();

    return response()->json([
        'status' => true,
        'data' => $requests
    ]);
}

public function pendingCommunityMemberRequest(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    // Communities managed by this user
    $communityIds = CommunityMembership::where('parent_id', $request->user_id)
        ->whereIn('role', ['super_admin', 'admin'])
        ->where('status', 1)
        ->pluck('community_id');

    // Pending requests
    $requests = CommunityMembership::with([
            'user',
            'community:id,name,profile'
        ])
        ->whereIn('community_id', $communityIds)
        ->where('status', 0)
        ->latest()
        ->get();

    return response()->json([
        'status' => true,
        'data' => $requests,
    ]);
}

public function approveCommunityMemberRequest_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id'    => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    $member = CommunityMembership::where('community_id', $request->community_id)
        ->where('parent_id', $request->parent_id)
        ->where('status', '0')
        ->first();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'Pending request not found.'
        ]);
    }

    $member->update([
        'status' => '1'
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Join request approved successfully.'
    ]);
}

public function approveCommunityMemberRequest(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id'   => 'required|exists:communities,id',
        'parent_id'      => 'required|exists:users,id',
        'user_id'        => 'required|exists:users,id', // Admin who approved
        'notification_id'=> 'nullable|exists:notifications,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    DB::beginTransaction();

    try {

        $community = Community::find($request->community_id);

        $member = CommunityMembership::where('community_id', $request->community_id)
            ->where('parent_id', $request->parent_id)
            ->where('status', 0)
            ->first();

        if (!$member) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Pending request not found.',
            ]);
        }
        $currentMembers = CommunityMembership::where('community_id', $community->id)
                          ->where('status', 1)
                          ->count();

        if ($currentMembers >= $this->maxCommunityMembers) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Community member limit reached. Maximum 100 members are allowed.'
            ], 422);
        }
        
        // Approve Request
        $member->update([
            'status' => 1
        ]);

        // Mark Admin Notification Completed
        if ($request->filled('notification_id')) {

            Notification::where('id', $request->notification_id)
                ->where('user_id', $request->user_id)
                ->update([
                    'isActionDone' => 1
                ]);
        }

        $user = User::find($request->parent_id);

        // Create User Notification
        $notification = Notification::create([
            'user_id'       => $user->id,
            'sender_id'     => $request->user_id,
            'notifiable_id' => $community->id,
            'type'          => 'community_join_request_approved',
            'title'         => 'Join Request Approved',
            'message'       => 'Your request to join "' . $community->name . '" has been approved.',
            'is_read'       => false,
        ]);

        // Push Notification
        if ($user && !empty($user->device_token)) {

            try {

                $this->fcm->sendNotification(
                    [$user->device_token],
                    [
                        'title' => 'Join Request Approved',
                        'body'  => 'Your request to join "' . $community->name . '" has been approved.',
                        'type' => 'community_join_request_approved',
                        'community_id' => (string)$community->id,
                        'notification_id' => (string)$notification->id,
                        'sender_id' => (string)$request->user_id,
                    ]
                );

            } catch (\Exception $e) {

                \Log::error(
                    'Community Approval Notification Error: ' .
                    $e->getMessage()
                );
            }
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Join request approved successfully.',
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error(
            'Approve Community Request Error: ' .
            $e->getMessage()
        );

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function rejectCommunityMemberRequest_old(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'parent_id'    => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ]);
    }

    $member = CommunityMembership::where('community_id', $request->community_id)
        ->where('parent_id', $request->parent_id)
        ->where('status', '0')
        ->first();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'Pending request not found.'
        ]);
    }

    $member->delete();

    return response()->json([
        'status' => true,
        'message' => 'Join request rejected successfully.'
    ]);
}

public function rejectCommunityMemberRequest(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id'   => 'required|exists:communities,id',
        'parent_id'      => 'required|exists:users,id',
        'user_id'        => 'required|exists:users,id', // Admin who rejected
        'notification_id'=> 'nullable|exists:notifications,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    DB::beginTransaction();

    try {

        $community = Community::find($request->community_id);

        $member = CommunityMembership::where('community_id', $request->community_id)
            ->where('parent_id', $request->parent_id)
            ->where('status', 0)
            ->first();

        if (!$member) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Pending request not found.',
            ]);
        }

        $user = User::find($request->parent_id);

        // Delete Pending Request
        $member->delete();

        // Mark Admin Notification Completed
        if ($request->filled('notification_id')) {

            Notification::where('id', $request->notification_id)
                ->where('user_id', $request->user_id)
                ->update([
                    'isActionDone' => 1
                ]);
        }

        // Create User Notification
        $notification = Notification::create([
            'user_id'       => $user->id,
            'sender_id'     => $request->user_id,
            'notifiable_id' => $community->id,
            'type'          => 'community_join_request_rejected',
            'title'         => 'Join Request Declined',
            'message'       => 'Your request to join "' . $community->name . '" was declined.',
            'is_read'       => false,
        ]);

        // Push Notification
        if ($user && !empty($user->device_token)) {

            try {

                $this->fcm->sendNotification(
                    [$user->device_token],
                    [
                        'title' => 'Join Request Declined',
                        'body'  => 'Your request to join "' . $community->name . '" was declined.',
                        'type' => 'community_join_request_rejected',
                        'community_id' => (string)$community->id,
                        'notification_id' => (string)$notification->id,
                        'sender_id' => (string)$request->user_id,
                    ]
                );

            } catch (\Exception $e) {

                \Log::error(
                    'Community Rejection Notification Error: ' .
                    $e->getMessage()
                );
            }
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Join request rejected successfully.',
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error(
            'Reject Community Request Error: ' .
            $e->getMessage()
        );

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function addCommunityMember(Request $request)
{
    $validator = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'user_id'      => 'required|exists:users,id', // Admin/Super Admin
        'parent_id'    => 'required|exists:users,id', // Member to add
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    DB::beginTransaction();

    try {

        $community = Community::find($request->community_id);

        // Verify permission
        $hasPermission = CommunityMembership::where('community_id', $community->id)
            ->where('parent_id', $request->user_id)
            ->whereIn('role', ['super_admin', 'admin'])
            ->where('status', 1)
            ->exists();

        if (!$hasPermission) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'You are not authorized to add members.',
            ], 403);
        }

        // Already member?
        $existingMember = CommunityMembership::where('community_id', $community->id)
            ->where('parent_id', $request->parent_id)
            ->first();

        if ($existingMember) {

            DB::rollBack();

            if ($existingMember->status == 1) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User is already a member of this community.',
                ]);
            }

            return response()->json([
                'status'  => false,
                'message' => 'User already has a pending join request.',
            ]);
        }
        
       $currentMembers = CommunityMembership::where('community_id', $community->id)
                         ->where('status', 1)
                         ->count();

        if ($currentMembers >= $this->maxCommunityMembers) {
    return response()->json([
        'status' => false,
        'message' => 'Community member limit reached. Maximum 100 members are allowed.'
    ], 422);
}

        // Add Member
        CommunityMembership::create([
            'community_id' => $community->id,
            'parent_id'    => $request->parent_id,
            'role'         => 'member',
            'status'       => 1,
        ]);

        $member = User::find($request->parent_id);
        $admin  = User::find($request->user_id);

        // Database Notification
        $notification = Notification::create([
            'user_id'       => $member->id,
            'sender_id'     => $admin->id,
            'notifiable_id' => $community->id,
            'type'          => 'community_member_added',
            'title'         => 'Added to Community',
            'message'       => $admin->first_name . ' added you to the "' . $community->name . '" community.',
            'is_read'       => false,
        ]);

        // Push Notification
        if (!empty($member->device_token)) {

            try {

                $this->fcm->sendNotification(
                    [$member->device_token],
                    [
                        'title' => 'Added to Community',
                        'body'  => $admin->first_name . ' added you to the "' . $community->name . '" community.',
                        'type' => 'community_member_added',
                        'community_id' => (string) $community->id,
                        'notification_id' => (string) $notification->id,
                        'sender_id' => (string) $admin->id,
                    ]
                );

            } catch (\Exception $e) {

                \Log::error(
                    'Community Member Added Notification Error: ' .
                    $e->getMessage()
                );
            }
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Member added successfully.',
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        \Log::error(
            'Add Community Member Error: ' .
            $e->getMessage()
        );

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}
