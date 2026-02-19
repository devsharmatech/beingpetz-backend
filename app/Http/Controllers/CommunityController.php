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
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CommunitiesExport;

class CommunityController extends Controller
{
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

public function getCommunities(Request $request)
{
    $communities = Community::with('creator','users','superAdmin','admins','members')->latest()->get();
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

public function joinCommunity(Request $request)
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
        return response()->json([
            'status' => false,
            'message' => 'Already joined this community!',
        ]);
    }

    CommunityMembership::create([
        'community_id' => $request->community_id,
        'parent_id' => $request->parent_id,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Joined successfully!',
    ]);
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
        ->paginate(10);
    return response()->json([
        'status' => true,
        'data' => $joined,
    ]);
   }


public function updateCommunityProfile(Request $request)
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

        // Update community basic info
        $community->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'description' => $request->description,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        $manager = new ImageManager(new Driver());

        // Handle profile image update with resizing
        if ($request->hasFile('profile')) {
            if ($community->profile && file_exists(public_path($community->profile))) {
                unlink(public_path($community->profile));
            }
            
            $file = $request->file('profile');
            $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/communities/' . $filename;

            $manager->read($file)->resize(400, 400)->save(public_path($path));
            $community->update(['profile' => $path]);
        }

        // Handle cover image update with resizing
        if ($request->hasFile('cover_image')) {
            if ($community->cover_image && file_exists(public_path($community->cover_image))) {
                unlink(public_path($community->cover_image));
            }
            
            $file = $request->file('cover_image');
            $filename = uniqid('cover_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/communities/' . $filename;

            $manager->read($file)->resize(1200, 400)->save(public_path($path));
            $community->update(['cover_image' => $path]);
        }

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


}
