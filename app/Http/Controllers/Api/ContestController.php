<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\{
    Contest,
    ContestEntry,
    ContestVote,
    ContestWinner
};
use App\Models\Pet;

class ContestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Helper Response
    |--------------------------------------------------------------------------
    */
    private function success($message, $data = null)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    private function error($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $code);
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Contest List (open/upcoming/past)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $type = $request->type;

        $query = Contest::query();

        if ($type === 'open') {
            $query->open();
        } elseif ($type === 'upcoming') {
            $query->upcoming();
        } elseif ($type === 'past') {
            $query->past();
        }

        $data = $query->latest()->get();

        return $this->success('Contest list fetched', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Contest Detail
    |--------------------------------------------------------------------------
    */
    public function show_old($id)
    {
        $contest = Contest::with([
            'entries' => function ($q) {
    $q->where('status', 'approved')
      ->with([
          'user',
          'pet'
      ])
      ->orderByDesc('votes')
      ->limit(10);
},
            'winners.entry'
        ])->find($id);

        if (!$contest) {
            return $this->error('Contest not found', 404);
        }

        return $this->success('Contest detail fetched', $contest);
    }

public function show($id)
{
    $contest = Contest::with([

        'entries' => function ($q) {
            $q->where('status', 'approved')
                ->with([
                    'user',
                    'pet',
                    'votes.user',
                    'votes.pet'
                ])
                ->withCount('votes')
                ->orderByDesc('votes_count')
                ->limit(10);
        },

        'winners.entry.user',
        'winners.entry.pet'

    ])->find($id);

    if (!$contest) {
        return $this->error('Contest not found', 404);
    }

    return $this->success('Contest detail fetched', $contest);
}

    /*
    |--------------------------------------------------------------------------
    | 3. Submit Entry
    |--------------------------------------------------------------------------
    */
public function submitEntry_nold(Request $request)
{
    $validator = Validator::make($request->all(), [
        'contest_id' => 'required|exists:contests,id',
        'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:20480',
        'caption' => 'nullable|string|max:500',
        'pet_id' => 'required|exists:pets,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data' => null
        ], 422);
    }

    $user = $request->user();
    // dd($user);
    if ($request->filled('pet_id')) {

    $pet = Pet::where('id', $request->pet_id)
        ->where('user_id', $user->id)
        ->first();

    if (!$pet) {
        return $this->error('This pet does not belong to your account.');
    }
}
    $contest = Contest::find($request->contest_id);

    // ✅ Check contest active
    if (now() < $contest->start_date || now() > $contest->end_date) {
        return response()->json([
            'success' => false,
            'message' => 'Contest is not active',
            'data' => null
        ]);
    }

    // ✅ Check entry limit
    $count = ContestEntry::where('contest_id', $contest->id)
        ->where('user_id', $user->id)
        ->count();

    if ($count >= $contest->max_entries_per_user) {
        return response()->json([
            'success' => false,
            'message' => 'Entry limit reached',
            'data' => null
        ]);
    }

    // 📁 FILE UPLOAD (MOVE)
    $file = $request->file('media');

    $uploadPath = public_path('uploads/contest');

    // ✅ Create folder if not exists
    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    // ✅ Generate unique filename
    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

    // ✅ Move file
    $file->move($uploadPath, $filename);

    // Save relative path
    $filePath = 'uploads/contest/' . $filename;

    // ✅ Create entry
    $entry = ContestEntry::create([
        'contest_id' => $contest->id,
        'user_id' => $user->id,
        'pet_id' => $request->pet_id,
        'media' => $filePath,
        'caption' => $request->caption
    ]);

    // ✅ Add full URL (important for mobile)
    $entry->media_url = asset($filePath);

    return response()->json([
        'success' => true,
        'message' => 'Entry submitted successfully',
        'data' => $entry
    ]);
}

public function submitEntry(Request $request)
{
    $validator = Validator::make($request->all(), [
        'contest_id' => 'required|exists:contests,id',
        'pet_id'     => 'required|exists:pets,id',
        'media'      => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:20480',
        'caption'    => 'nullable|string|max:500',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data'    => null
        ], 422);
    }

    $user = $request->user();

    // Verify pet belongs to logged in parent
    $pet = Pet::where('id', $request->pet_id)
        ->where('user_id', $user->id)
        ->first();

    if (!$pet) {
        return response()->json([
            'success' => false,
            'message' => 'This pet does not belong to your account.',
            'data'    => null
        ], 403);
    }

    $contest = Contest::find($request->contest_id);

    if (!$contest) {
        return response()->json([
            'success' => false,
            'message' => 'Contest not found.',
            'data'    => null
        ], 404);
    }

    // Check contest is active
    if (now()->lt($contest->start_date) || now()->gt($contest->end_date)) {
        return response()->json([
            'success' => false,
            'message' => 'Contest is not active.',
            'data'    => null
        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | Check Entry Limit Per Pet
    |--------------------------------------------------------------------------
    | A parent can own multiple pets.
    | Every pet can participate independently.
    | Limit applies to each pet, NOT the parent.
    |--------------------------------------------------------------------------
    */

    $entryCount = ContestEntry::where('contest_id', $contest->id)
        ->where('pet_id', $request->pet_id)
        ->count();

    if ($entryCount >= $contest->max_entries_per_user) {
        return response()->json([
            'success' => false,
            'message' => 'This pet has reached the maximum allowed entries for this contest.',
            'data'    => null
        ], 422);
    }

    // Upload media
    $file = $request->file('media');

    $uploadPath = public_path('uploads/contest');

    if (!file_exists($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }

    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

    $file->move($uploadPath, $filename);

    $filePath = 'uploads/contest/' . $filename;

    // Create contest entry
    $entry = ContestEntry::create([
        'contest_id' => $contest->id,
        'user_id'    => $user->id,
        'pet_id'     => $pet->id,
        'media'      => $filePath,
        'caption'    => $request->caption,
    ]);

    // Return media URL
    $entry->media_url = asset($filePath);

    return response()->json([
        'success' => true,
        'message' => 'Contest entry submitted successfully.',
        'data'    => $entry,
    ], 201);
}

    /*
    |--------------------------------------------------------------------------
    | 4. Vote Entry
    |--------------------------------------------------------------------------
    */
    public function vote_old(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'entry_id' => 'required|exists:contest_entries,id'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $user = $request->user();

        // Prevent duplicate vote
        $exists = ContestVote::where('entry_id', $request->entry_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return $this->error('Already voted');
        }

        ContestVote::create([
            'entry_id' => $request->entry_id,
            'user_id' => $user->id,
            'ip_address' => $request->ip()
        ]);

        ContestEntry::where('id', $request->entry_id)->increment('votes');

        return $this->success('Vote submitted successfully');
    }

public function vote__(Request $request)
{
    $validator = Validator::make($request->all(), [
        'entry_id' => 'required|exists:contest_entries,id',
        'pet_id'   => 'required|exists:pets,id',
    ]);

    if ($validator->fails()) {
        return $this->error($validator->errors()->first(), 422);
    }

    $user = $request->user();

    // Verify pet belongs to logged-in user
    $pet = Pet::where('id', $request->pet_id)
        ->where('user_id', $user->id)
        ->first();

    if (!$pet) {
        return $this->error('This pet does not belong to your account.');
    }

    // Get contest entry
   // Get approved contest entry only
$entry = ContestEntry::where('id', $request->entry_id)
    ->where('status', 'approved')
    ->first();

if (!$entry) {
    return $this->error('Contest entry not found or not approved.', 404);
}

    // Get contest
    $contest = Contest::findOrFail($entry->contest_id);

    if (!$contest) {
        return $this->error('Contest not found.', 404);
    }

    // Contest must be active
    if (now()->lt($contest->start_date) || now()->gt($contest->end_date)) {
        return $this->error('Contest is not active.');
    }

    // Prevent self voting
    if ($entry->user_id == $user->id) {
        return $this->error('You cannot vote for your own entry.');
    }

    // One vote per pet per contest
    $alreadyVoted = ContestVote::where('contest_id', $contest->id)
        ->where('pet_id', $request->pet_id)
        ->exists();

    if ($alreadyVoted) {
        return $this->error('This pet has already voted in this contest.');
    }

    // Save vote
    ContestVote::create([
        'contest_id' => $contest->id,
        'entry_id'   => $entry->id,
        'user_id'    => $user->id,
        'pet_id'     => $pet->id,
    ]);

    // Increase vote count
    $entry->increment('votes');

    return $this->success('Vote submitted successfully.', [
        'entry_id' => $entry->id,
        'pet_id'   => $pet->id,
        'votes'    => $entry->fresh()->votes,
    ]);
}

public function vote(Request $request)
{
    $validator = Validator::make($request->all(), [
        'entry_id' => 'required|exists:contest_entries,id',
    ]);

    if ($validator->fails()) {
        return $this->error($validator->errors()->first(), 422);
    }

    $user = $request->user();

    // Get approved contest entry
    $entry = ContestEntry::where('id', $request->entry_id)
        ->where('status', 'approved')
        ->first();

    if (!$entry) {
        return $this->error('Contest entry not found or not approved.', 404);
    }

    // Get contest
    $contest = Contest::find($entry->contest_id);

    if (!$contest) {
        return $this->error('Contest not found.', 404);
    }

    // Contest must be active
    if (now()->lt($contest->start_date) || now()->gt($contest->end_date)) {
        return $this->error('Contest is not active.');
    }

    // Prevent self voting
    if ($entry->user_id == $user->id) {
        return $this->error('You cannot vote for your own entry.');
    }

    // One vote per parent per contest
    $alreadyVoted = ContestVote::where('contest_id', $contest->id)
        ->where('user_id', $user->id)
        ->exists();

    if ($alreadyVoted) {
        return $this->error('You have already voted in this contest.');
    }

    // Save vote
    ContestVote::create([
        'contest_id' => $contest->id,
        'entry_id'   => $entry->id,
        'user_id'    => $user->id,
    ]);

    // Increase vote count
    $entry->increment('votes');

    return $this->success('Vote submitted successfully.', [
        'entry_id' => $entry->id,
        'votes'    => $entry->fresh()->votes,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | 5. Leaderboard
    |--------------------------------------------------------------------------
    */
    public function leaderboard($contest_id)
    {
    $entries = ContestEntry::where('contest_id', $contest_id)
        ->with([
            'user:id,first_name,last_name,username,profile',
            'pet:id,name,type,breed,avatar'
        ])
        ->orderByDesc('votes')
        ->take(20)
        ->get([
            'id',
            'user_id',
            'pet_id',
            'media',
            'votes',
            'is_winner',
            'caption'
        ]);

    $data = $entries->values()->map(function ($entry, $index) {

        return [
            'rank' => $index + 1,
            'id' => $entry->id,
            'votes' => $entry->votes,
            'media_url' => url($entry->media),
            'caption' => $entry->caption,
            'is_winner' => $entry->is_winner,

            'user' => [
                'id' => $entry->user->id ?? null,
                'name' => trim(($entry->user->first_name ?? '') . ' ' . ($entry->user->last_name ?? '')),
                'username' => $entry->user->username ?? null,
                'profile_url' => isset($entry->user->profile) ? url($entry->user->profile) : null,
            ],

            'pet' => $entry->pet ? [
                'id' => $entry->pet->id,
                'name' => $entry->pet->name,
                'type' => $entry->pet->type,
                'breed' => $entry->pet->breed,
                'avatar_url' => isset($entry->pet->avatar) ? url($entry->pet->avatar) : null,
            ] : null,
        ];
    });

    return $this->success('Leaderboard fetched', $data);
}

    /*
    |--------------------------------------------------------------------------
    | 6. My Entries
    |--------------------------------------------------------------------------
    */
    public function myEntries(Request $request)
    {
        $user = $request->user();
        
        $entries = ContestEntry::where('user_id', $user->id)
            ->with('contest')
            ->latest()
            ->get();
        
        return $this->success('My entries fetched', $entries);
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Contest Winners
    |--------------------------------------------------------------------------
    */
    public function winners($contest_id)
    {
        $winners = ContestWinner::where('contest_id', $contest_id)
            ->with('entry.user')
            ->get();

        return $this->success('Winners fetched', $winners);
    }
}