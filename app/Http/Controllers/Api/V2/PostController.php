<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\V2Post;
use App\Models\V2\V2PostImage;
use App\Models\V2\V2PostVideo;
use App\Models\Hashtag;
use App\Models\User;
use App\Models\Notification;
use App\Services\V2\ValidationService;
use App\Services\V2\ContentModerationService;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostController extends Controller
{
    protected $fcm;

    public function __construct()
    {
        $projectId = config('services.firebase.project_id');
        $credentialsPath = public_path(config('services.firebase.credentials_path'));

        $this->fcm = new FirebaseService($projectId, $credentialsPath);
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            // 1. Get List of Accepted Friends
            $friendIds = DB::table('friend_requests')
                ->where(function ($query) use ($user) {
                    $query->where('from_parent_id', $user->id)
                        ->orWhere('to_parent_id', $user->id);
                })
                ->where('status', 'accepted')
                ->get()
                ->map(function ($friend) use ($user) {
                    return $friend->from_parent_id == $user->id ? $friend->to_parent_id : $friend->from_parent_id;
                })
                ->toArray();

            // 2. Self and Friends pool
            $priorityUserIds = array_merge([$user->id], $friendIds);

            // 3. Get Blocked Users (Assuming a 'blocks' table might exist, fallback to empty array if not)
            $blockedUserIds = [];
            if (Schema::hasTable('blocked_users')) {
                $blockedUserIds = DB::table('blocked_users')
                    ->where('user_id', $user->id)
                    ->pluck('blocked_user_id')
                    ->toArray();
            }

            // 4. Main Query: 
            // - Priority: Friends/Self (any active status)
            // - Discovery: Public posts from others (excluding blocked)
            $postsQuery = V2Post::with([
                'images',
                'videos',
                'taggedUsers',
                'user',
                'parent',
                'pet',
                'repost',
                'repost.user',
                'repost.parent',
                'repost.pet',
                'repost.images',
                'repost.videos',
                'repost.taggedUsers'
            ])
                ->withCount(['likes', 'shares', 'comments', 'reposts'])
                ->withExists([
                  'likes as is_liked' => function ($q) use ($user) {
                   $q->where('liked_by_id', $user->id)->where('liked_by_type', 'parent');
                 }
                ])
                ->where('status', 'active')
                ->whereNotIn('parent_id', $blockedUserIds)
                ->where(function ($query) use ($priorityUserIds) {
                    $query->whereIn('parent_id', $priorityUserIds) // Priority content
                        ->orWhere('is_public', 1); // Public discovery content
                });

            // 5. Optimized Sorting (Instagram-like): 
            $priorityString = implode(',', array_filter($priorityUserIds));

            if (!empty($priorityString)) {
                $postsQuery->orderByRaw("CASE WHEN parent_id IN ($priorityString) THEN 0 ELSE 1 END ASC");
            }

            $posts = $postsQuery->orderBy('created_at', 'desc')->simplePaginate($perPage);

            // Add human timing and format each post consistently
            $posts->through(function ($post) {
                return $this->formatPostResponse($post);
            });

            return response()->json([
                'status' => true,
                'data' => $posts,
                'page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'has_more' => $posts->hasMorePages(),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


public function userPosts(Request $request)
{
    try {
        $authUser = $request->user();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userId = $request->user_id;
        $perPage = (int) $request->input('per_page', 10);

        $posts = V2Post::with([
                'images',
                'videos',
                'taggedUsers',
                'user',
                'parent',
                'pet',
                'repost',
                'repost.user',
                'repost.parent',
                'repost.pet',
                'repost.images',
                'repost.videos',
                'repost.taggedUsers'
            ])
            ->withCount(['likes', 'shares', 'comments', 'reposts'])
            ->withExists([
                'likes as is_liked' => function ($q) use ($authUser) {
                    $q->where('liked_by_id', $authUser->id)
                      ->where('liked_by_type', 'parent');
                }
            ])
            ->where('status', 'active')
            ->where('parent_id', $userId)
            ->orderBy('created_at', 'desc')
            ->simplePaginate($perPage);

        $posts->through(function ($post) {
            return $this->formatPostResponse($post);
        });

        return response()->json([
            'status' => true,
            'message' => 'User posts fetched successfully.',
            'data' => $posts,
            'page' => $posts->currentPage(),
            'per_page' => $posts->perPage(),
            'has_more' => $posts->hasMorePages(),
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to retrieve user posts.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function getPostsByHashtag(Request $request, $hashtag)
    {
    try {

        $user = $request->user();

        $perPage = (int) $request->input('per_page', 10);

        // Get blocked users
        $blockedUserIds = [];

        if (Schema::hasTable('blocked_users')) {

            $blockedUserIds = DB::table('blocked_users')
                ->where('user_id', $user->id)
                ->pluck('blocked_user_id')
                ->toArray();
        }

        // remove # if frontend sends it
        $hashtag = ltrim(strtolower($hashtag), '#');

        // find hashtag
        $hashtagModel = Hashtag::where('name', $hashtag)->first();

        if (!$hashtagModel) {

            return response()->json([
                'success' => true,
                'message' => 'No posts found for this hashtag.',
                'data' => [],
            ], 200);
        }

        // get posts
        $posts = V2Post::with([
                'images',
                'videos',
                'taggedUsers',
                'user',
                'parent',
                'pet',
                'repost',
                'repost.user',
                'repost.parent',
                'repost.pet',
                'repost.images',
                'repost.videos',
                'repost.taggedUsers'
            ])
            ->withCount([
                'likes',
                'shares',
                'comments',
                'reposts'
            ])
            ->withExists([
                  'likes as is_liked' => function ($q) use ($user) {
                   $q->where('liked_by_id', $user->id)->where('liked_by_type', 'parent');
                 }
                ])
            ->where('status', 'active')
            
            ->whereNotIn('parent_id', $blockedUserIds)
            ->where(function ($query) use ($user) {

                // own posts OR public posts
                $query->where('parent_id', $user->id)
                      ->orWhere('is_public', 1);
            })
            ->whereHas('hashtags', function ($query) use ($hashtagModel) {

                $query->where('hashtags.id', $hashtagModel->id);
            })
            ->orderBy('created_at', 'desc')
            ->simplePaginate($perPage);

        // format response
        $posts->through(function ($post) {

            return $this->formatPostResponse($post);
        });

        return response()->json([
            'success' => true,
            'hashtag' => '#' . $hashtag,
            'data' => $posts,
            'page' => $posts->currentPage(),
            'per_page' => $posts->perPage(),
            'has_more' => $posts->hasMorePages(),
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve hashtag posts.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function myPosts(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 20);

            $posts = V2Post::with(['images', 'videos', 'taggedUsers'])
                ->withCount(['reposts']) 
                ->withExists([
                  'likes as is_liked' => function ($q) use ($user) {
                   $q->where('liked_by_id', $user->id)->where('liked_by_type', 'parent');
                 }
                ])
                ->where('parent_id', $user->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'posts' => $posts->map(function ($post) {
                        return $this->formatPostResponse($post);
                    }),
                    'pagination' => [
                        'current_page' => $posts->currentPage(),
                        'last_page' => $posts->lastPage(),
                        'per_page' => $posts->perPage(),
                        'total' => $posts->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your posts.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function petPosts(Request $request, $id)
{
    try {
        $authUser = $request->user();
        $perPage = (int) $request->input('per_page', 20);

        $posts = V2Post::with([
                'images',
                'videos',
                'taggedUsers',
                'user',
                'parent',
                'pet',
                'repost',
                'repost.user',
                'repost.parent',
                'repost.pet',
                'repost.images',
                'repost.videos',
                'repost.taggedUsers'
            ])
            ->withCount(['likes', 'shares', 'comments', 'reposts'])
            ->when($authUser, function ($query) use ($authUser) {
                $query->withExists([
                    'likes as is_liked' => function ($q) use ($authUser) {
                        $q->where('liked_by_id', $authUser->id)
                          ->where('liked_by_type', 'parent');
                    }
                ]);
            })
            ->where('pet_id', $id)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $formattedPosts = $posts->getCollection()->map(function ($post) {
            return $this->formatPostResponse($post);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pet posts fetched successfully.',
            'data' => [
                'posts' => $formattedPosts,
                'pagination' => [
                    'current_page' => $posts->currentPage(),
                    'last_page' => $posts->lastPage(),
                    'per_page' => $posts->perPage(),
                    'total' => $posts->total(),
                    'has_more_pages' => $posts->hasMorePages(),
                ]
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve pet posts.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'content' => 'nullable|string|max:5000',
                'media_url' => 'nullable|array',
                'media_url.*' => 'nullable|string|max:500',
                'is_public' => 'nullable|boolean',
                'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'featured_video' => 'nullable|mimes:mp4,mov,avi',
                'post_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                'post_videos.*' => 'nullable|mimes:mp4,mov,avi',
                'tagged_user_ids' => 'nullable|array',
                'tagged_user_ids.*' => 'exists:users,id',
                'pet_id' => 'nullable|exists:pets,id',
                'posted_by_type' => 'required|in:parent,pet',
                'posted_by_id' => 'required|integer',
                
                'feeling' => 'nullable|string|max:255',
                'activity' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Validate posted_by ownership
            $ownershipValidation = ValidationService::validatePostedBy(
                $user->id,
                $validated['posted_by_type'],
                $validated['posted_by_id']
            );

            if (!$ownershipValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $ownershipValidation['message']
                ], 403);
            }

            // Content moderation check (V2: Keywords -> AI -> Logging)
            $content = $validated['content'] ?? '';
            // if (!empty($content)) {
            //     $moderationResult = ContentModerationService::validate(
            //         $content,
            //         null, 
            //         $user->id,
            //         'post',
            //         $request->ip(),
            //         $request->userAgent()
            //     );

            //     if ($moderationResult['action'] === 'blocked') {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Your content violates our community guidelines and cannot be posted.',
            //             'data' => [
            //                 'violation_type' => $moderationResult['violation_type'],
            //                 'reason' => $moderationResult['reason'] ?? 'Violation detected'
            //             ]
            //         ], 422);
            //     }
            // }

            // Sanitize content
            $sanitized = ValidationService::sanitizeInput([
                'content' => $validated['content'] ?? '',
            ]);
            $content = $sanitized['content'] ?? '';

           preg_match_all('/#(\w+)/', $content, $matches);

           $hashtags = collect($matches[1])->map(fn($tag) => strtolower($tag))->unique()->values()->toArray();
    
    
    // mentions
preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $mentionMatches);

$usernames = collect($mentionMatches[1])
    ->map(fn($u) => strtolower($u))
    ->unique()
    ->values()
    ->toArray();
    
            // Create post
            $post = V2Post::create([
                'parent_id' => $user->id,
                'slug' => Str::slug(Str::random(6) . '-' . now()->timestamp),
                'pet_id' => $validated['pet_id'] ?? null,
                'posted_by_type' => $validated['posted_by_type'],
                'posted_by_id' => $validated['posted_by_id'],
                'content' => $sanitized['content'],
                'is_public' => $validated['is_public'] ?? true,
                'media_urls' => $validated['media_url'] ?? [],
                'status' => 'active',
                'moderation_reason' => null,
                'feeling' => $validated['feeling'] ?? null,
                'activity' => $validated['activity'] ?? null,
            ]);
              
            if (!empty($hashtags)) {

    foreach ($hashtags as $tag) {

        // create or get hashtag
        $hashtag = Hashtag::firstOrCreate([
            'name' => $tag
        ]);

        // check duplicate in same post
        $exists = DB::table('post_hashtags')
            ->where('post_id', $post->id)
            ->where('hashtag_id', $hashtag->id)
            ->exists();

        if (!$exists) {

            // insert into pivot
            DB::table('post_hashtags')->insert([
                'post_id' => $post->id,
                'hashtag_id' => $hashtag->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // increment usage count
            $hashtag->increment('usage_count');
        }
        }
     }  
             
             $mentionedUsers = User::whereIn('username', $usernames)
                               ->get(['id', 'username', 'device_token', 'first_name']);
             
             
             $mentionedUserIds = $mentionedUsers->pluck('id')->toArray();



             
            // Handle Media Uploads
            $manager = new ImageManager(new Driver());

            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                $image = $manager->read($file);
                $filename = uniqid('featured_') . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/posts/' . $filename;
                $image->save(public_path($path), 100);
                $post->featured_image = $path;
                $post->save();
            }

            if ($request->hasFile('featured_video')) {
                $file = $request->file('featured_video');
                $filename = uniqid('featured_video_') . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/posts/videos'), $filename);
                $post->featured_video = 'uploads/posts/videos/' . $filename;
                $post->save();
            }

            if ($request->hasFile('post_images')) {
                foreach ($request->file('post_images') as $file) {
                    $image = $manager->read($file);
                    $filename = uniqid('post_image_') . '.' . $file->getClientOriginalExtension();
                    $path = 'uploads/posts/' . $filename;
                    $image->save(public_path($path), 100);

                    V2PostImage::create([
                        'post_id' => $post->id,
                        'image_path' => $path,
                    ]);
                }
            }

            if ($request->hasFile('post_videos')) {
                foreach ($request->file('post_videos') as $file) {
                    $filename = uniqid('post_video_') . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/posts/videos'), $filename);

                    V2PostVideo::create([
                        'post_id' => $post->id,
                        'video_path' => 'uploads/posts/videos/' . $filename,
                    ]);
                }
            }

            // Handle Tagged Users
            if (!empty($validated['tagged_user_ids'])) {
                $post->taggedUsers()->sync($validated['tagged_user_ids']);

                foreach ($validated['tagged_user_ids'] as $taggedUserId) {
                    if ($taggedUserId != $user->id) {
                        // DB Notification
                        $notification = Notification::create([
                            'user_id' => $taggedUserId,
                            'sender_id' => $user->id,
                            'notifiable_id' => $post->id,
                            'type' => 'tagged_in_post',
                            'title' => 'You were tagged in a post',
                            'message' => 'You were tagged in a post.',
                            'is_read' => false,
                        ]);

                        // FCM Notification
                        $taggedUser = User::find($taggedUserId);
                        if ($taggedUser && $taggedUser->device_token) {
                            try {
                                $this->fcm->sendNotification(
                                    [$taggedUser->device_token],
                                    [
                                        'title' => 'You were tagged in a post',
                                        'body' => 'You were tagged in a post by ' . ($user->first_name ?? 'someone'),
                                        'sender_id' => (string) $user->id,
                                        'type' => 'tagged_in_post',
                                        'notification_id' => (string) $notification->id,
                                        'notifiable_id' => (string) $post->id
                                    ]
                                );
                            } catch (\Exception $e) {
                                Log::error("Tag notification failed: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
            
            // merge with manual tagged users if exist
$allTaggedIds = array_unique(array_merge(
    $mentionedUserIds,
    $validated['tagged_user_ids'] ?? []
));

// attach
if (!empty($allTaggedIds)) {
    $post->taggedUsers()->sync($allTaggedIds);
}

            
            // Send notifications to friends about the new post
            $friends = DB::table('friend_requests')->where(function ($q) use ($user) {
                $q->where('from_parent_id', $user->id)
                    ->orWhere('to_parent_id', $user->id);
            })->where('status', 'accepted')->get();

            foreach ($friends as $friend) {
                $friendId = $friend->from_parent_id == $user->id
                    ? $friend->to_parent_id
                    : $friend->from_parent_id;

                if ($friendId == $user->id)
                    continue;

                $c_name = $user->first_name ?? 'Someone';
                $notification = Notification::create([
                    'user_id' => $friendId,
                    'sender_id' => $user->id,
                    'notifiable_id' => $post->id,
                    'type' => 'new_post',
                    'title' => 'New post from ' . $c_name,
                    'message' => $c_name . ' shared a new post',
                    'is_read' => false,
                ]);

                $friendUser = User::find($friendId);
                if ($friendUser && $friendUser->device_token) {
                    try {
                        $this->fcm->sendNotification(
                            [$friendUser->device_token],
                            [
                                'title' => 'New post from ' . $c_name,
                                'body' => mb_substr($post->content, 0, 100) . (mb_strlen($post->content) > 100 ? '...' : ''),
                                'sender_id' => (string) $user->id,
                                'type' => 'new_post',
                                'notification_id' => (string) $notification->id,
                                'notifiable_id' => (string) $post->id
                            ]
                        );
                    } catch (\Exception $e) {
                        Log::error("New post notification failed: " . $e->getMessage());
                    }
                }
            }

            // Log if flagged by moderation
            if (!empty($content) && ($moderationResult['action'] ?? '') === 'flagged') {
                ContentModerationService::logModeration(
                    $user->id,
                    'post',
                    $post->id,
                    $content,
                    $moderationResult,
                    $request->ip(),
                    $request->userAgent()
                );
            }
            
            foreach ($mentionedUsers as $mentionedUser) {

   
    if ($mentionedUser->id == $user->id) {
        continue;
    }

    // DB notification
    $notification = Notification::create([
        'user_id' => $mentionedUser->id,
        'sender_id' => $user->id,
        'notifiable_id' => $post->id,
        'type' => 'tagged_in_post',
        'title' => 'You were mentioned in a post',
        'message' => 'You were mentioned by ' . ($user->first_name ?? 'someone'),
        'is_read' => false,
    ]);

    // FCM push
    if ($mentionedUser->device_token) {
        try {
            $this->fcm->sendNotification(
                [$mentionedUser->device_token],
                [
                    'title' => 'You were mentioned in a post',
                    'body'  => ($user->first_name ?? 'Someone') . ' mentioned you in a post',
                    'sender_id' => (string) $user->id,
                    'type' => 'tagged_in_post',
                    'notification_id' => (string) $notification->id,
                    'notifiable_id' => (string) $post->id
                ]
            );
        } catch (\Exception $e) {
            \Log::error("Mention notification failed: " . $e->getMessage());
        }
    }
}

            $post->load(['images', 'videos', 'taggedUsers']);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $this->formatPostResponse($post)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create post.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function show($id)
    {
        try {
            $post = V2Post::with([
                'comments' => function ($query) {
                    $query->where('status', 'active')->orderBy('created_at', 'desc');
                },
                'images',
                'videos',
                'taggedUsers',
                'user',
                'parent',
                'pet',
                'repost',
                'repost.user',
                'repost.parent',
                'repost.pet',
                'repost.images',
                'repost.videos',
                'repost.taggedUsers'
            ])->find($id);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatPostResponse($post, true)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve post.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
    try {
        $user = $request->user();

        $post = V2Post::where('id', $id)
            ->where('parent_id', $user->id)
            ->first();

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found or unauthorized'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string|max:5000',
            'media_url' => 'nullable|array',
            'media_url.*' => 'nullable|string|max:500',
            'is_public' => 'nullable|boolean',

            'featured_image' => 'nullable|image',
            'featured_video' => 'nullable|mimes:mp4,mov,avi',

            'post_images.*' => 'nullable|image',
            'post_videos.*' => 'nullable|mimes:mp4,mov,avi',

            'tagged_user_ids' => 'nullable|array',
            'tagged_user_ids.*' => 'exists:users,id',

            'pet_id' => 'nullable|exists:pets,id',
            'posted_by_type' => 'nullable|in:parent,pet',
            'posted_by_id' => 'nullable|integer',

            'feeling' => 'nullable|string|max:255',
            'activity' => 'nullable|string|max:255',
            'remove_featured_image' => 'nullable|boolean',
            'remove_featured_video' => 'nullable|boolean',
            
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'exists:post_images,id',

            'remove_video_ids' => 'nullable|array',
            'remove_video_ids.*' => 'exists:post_videos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (!empty($validated['posted_by_type']) && !empty($validated['posted_by_id'])) {
            $ownershipValidation = ValidationService::validatePostedBy(
                $user->id,
                $validated['posted_by_type'],
                $validated['posted_by_id']
            );

            if (!$ownershipValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $ownershipValidation['message']
                ], 403);
            }
        }

        $sanitized = ValidationService::sanitizeInput([
            'content' => $validated['content'] ?? $post->content,
        ]);

        $content = $sanitized['content'] ?? '';

        $post->update([
            'content' => $content,
            'is_public' => $validated['is_public'] ?? $post->is_public,
            'media_urls' => $validated['media_url'] ?? $post->media_urls,
            'pet_id' => $validated['pet_id'] ?? $post->pet_id,
            'posted_by_type' => $validated['posted_by_type'] ?? $post->posted_by_type,
            'posted_by_id' => $validated['posted_by_id'] ?? $post->posted_by_id,
            'feeling' => $validated['feeling'] ?? $post->feeling,
            'activity' => $validated['activity'] ?? $post->activity,
        ]);

        // Hashtags update
        preg_match_all('/#(\w+)/', $content, $matches);

        $hashtags = collect($matches[1])
            ->map(fn($tag) => strtolower($tag))
            ->unique()
            ->values()
            ->toArray();

        DB::table('post_hashtags')->where('post_id', $post->id)->delete();

        foreach ($hashtags as $tag) {
            $hashtag = Hashtag::firstOrCreate(['name' => $tag]);

            DB::table('post_hashtags')->insert([
                'post_id' => $post->id,
                'hashtag_id' => $hashtag->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $hashtag->increment('usage_count');
        }

        // Mentions update
        preg_match_all('/@([a-zA-Z0-9_]+)/', $content, $mentionMatches);

        $usernames = collect($mentionMatches[1])
            ->map(fn($u) => strtolower($u))
            ->unique()
            ->values()
            ->toArray();

        $mentionedUsers = User::whereIn('username', $usernames)
            ->get(['id', 'username', 'device_token', 'first_name']);

        $mentionedUserIds = $mentionedUsers->pluck('id')->toArray();

        $allTaggedIds = array_unique(array_merge(
            $mentionedUserIds,
            $validated['tagged_user_ids'] ?? []
        ));

        $post->taggedUsers()->sync($allTaggedIds);

        // Media uploads
        $manager = new ImageManager(new Driver());
        // Remove existing featured image
        if ($request->remove_featured_image==1) {
        
         if (!empty($post->featured_image)) {

        $oldImage = public_path($post->featured_image);

        if (file_exists($oldImage)) {

            if (!@unlink($oldImage)) {

                \Log::warning(
                    'Unable to delete featured image: ' . $oldImage
                );
            }
        }

        $post->featured_image = null;
    }
         $post->save();
       }

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $image = $manager->read($file);

            $filename = uniqid('featured_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/posts/' . $filename;

            $image->save(public_path($path), 100);

            $post->featured_image = $path;
            $post->save();
        }

        // Remove existing featured video
        if ($request->remove_featured_video==1) {

    if (!empty($post->featured_video)) {

        $oldVideo = public_path($post->featured_video);

        if (file_exists($oldVideo)) {

            if (!@unlink($oldVideo)) {

                \Log::warning(
                    'Unable to delete featured video: ' . $oldVideo
                );
            }
        }

        $post->featured_video = null;
    }
    $post->save();
}

        if ($request->hasFile('featured_video')) {
            $file = $request->file('featured_video');

            $filename = uniqid('featured_video_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/posts/videos'), $filename);

            $post->featured_video = 'uploads/posts/videos/' . $filename;
            $post->save();
        }
        
        if (!empty($validated['remove_image_ids'])) {

    $images = V2PostImage::where('post_id', $post->id)
        ->whereIn('id', $validated['remove_image_ids'])
        ->get();

    foreach ($images as $image) {

        $file = public_path($image->image_path);

        if (file_exists($file)) {
            @unlink($file);
        }

        $image->delete();
    }
}
        
        if ($request->hasFile('post_images')) {
            foreach ($request->file('post_images') as $file) {
                $image = $manager->read($file);

                $filename = uniqid('post_image_') . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/posts/' . $filename;

                $image->save(public_path($path), 100);

                V2PostImage::create([
                    'post_id' => $post->id,
                    'image_path' => $path,
                ]);
            }
        }

        if (!empty($validated['remove_video_ids'])) {

    $videos = V2PostVideo::where('post_id', $post->id)
        ->whereIn('id', $validated['remove_video_ids'])
        ->get();

    foreach ($videos as $video) {

        $file = public_path($video->video_path);

        if (file_exists($file)) {
            @unlink($file);
        }

        $video->delete();
    }
}

        if ($request->hasFile('post_videos')) {
            foreach ($request->file('post_videos') as $file) {
                $filename = uniqid('post_video_') . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('uploads/posts/videos'), $filename);

                V2PostVideo::create([
                    'post_id' => $post->id,
                    'video_path' => 'uploads/posts/videos/' . $filename,
                ]);
            }
        }

        $post->load(['images', 'videos', 'taggedUsers']);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully',
            'data' => $this->formatPostResponse($post)
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update post.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Remove the specified post.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $post = V2Post::with(['images', 'videos'])->find($id);

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            // Check ownership
            if ($post->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this post.'
                ], 403);
            }

            if ($post->featured_image && File::exists(public_path($post->featured_image))) {
                File::delete(public_path($post->featured_image));
            }

            if ($post->featured_video && File::exists(public_path($post->featured_video))) {
                File::delete(public_path($post->featured_video));
            }

            foreach ($post->images as $img) {
                if ($img->image_path && File::exists(public_path($img->image_path))) {
                    File::delete(public_path($img->image_path));
                }
                $img->delete();
            }

            foreach ($post->videos as $vid) {
                if ($vid->video_path && File::exists(public_path($vid->video_path))) {
                    File::delete(public_path($vid->video_path));
                }
                $vid->delete();
            }

            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete post.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format post response.
     *
     * @param V2Post $post
     * @param bool $includeComments
     * @return array
     */
    protected function formatPostResponse(V2Post $post, bool $includeComments = false): array
    {
        // Helper to slim user data
        $slimUser = function($u) {
            if (!$u) return null;
            return [
                'id' => $u->id,
                'first_name' => $u->first_name,
                'last_name' => $u->last_name,
                'username' => $u->username,
                'profile' => $u->profile,
                'role' => $u->role,
            ];
        };

        $data = [
            'id' => $post->id,
            'parent_id' => $post->parent_id,
            // 'posted_by_type' => $post->posted_by_type,
            'posted_by_id' => $post->posted_by_id,
            'slug' => $post->slug,
            'content' => $post->content,
            'is_public' => (int) $post->is_public,
            'featured_image' => $post->featured_image,
            'featured_video' => $post->featured_video,
            'media_urls' => $post->media_urls,
            'status' => $post->status,
            'moderation_reason' => $post->moderation_reason,
            'feeling' => $post->feeling,
            'activity' => $post->activity,
            'repost_id' => $post->repost_id,
            'forward_to_user_id' => $post->forward_to_user_id,
            'pet_id' => $post->pet_id,
            'post_type' => $post->post_type,
            'deleted_at' => (int)$post->deleted_at,
            'likes_count' => $post->likes_count ?? $post->likes()->count(),
            'is_liked' => (bool) ($post->is_liked ?? false),
            'shares_count' => $post->shares_count ?? $post->shares()->count(),
            'comments_count' => $post->comments_count ?? $post->comments()->where('status', 'active')->count(),
            'reposts_count' => $post->reposts_count ?? $post->reposts()->count(),
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'created_at_human' => $post->created_at->diffForHumans(),
        ];

        // Process images
        $data['images'] = $post->images->map(function($img) {
            return [
                'id' => $img->id,
                'post_id' => $img->post_id,
                'image_path' => $img->image_path,
                'display_order' => $img->display_order,
                'created_at' => $img->created_at,
                'updated_at' => $img->updated_at,
            ];
        })->toArray();

        // Process videos
        $data['videos'] = $post->videos->map(function($vid) {
            return [
                'id' => $vid->id,
                'post_id' => $vid->post_id,
                'video_path' => $vid->video_path,
                'display_order' => $vid->display_order,
                'created_at' => $vid->created_at,
                'updated_at' => $vid->updated_at,
            ];
        })->toArray();

        // Tagged users
        $data['tagged_users'] = $post->relationLoaded('taggedUsers') ? $post->taggedUsers->map(function ($u) use ($slimUser) {
            return $slimUser($u);
        })->toArray() : [];

        // Authors (Slimmed)
        $data['user'] = $slimUser($post->user);
        $data['parent'] = $slimUser($post->parent); // Alias for user for V1 compatibility
        $data['pet'] = $post->pet ? [
            'id' => $post->pet->id,
            'name' => $post->pet->name,
            'profile_pic' => $post->pet->profile_pic,
            'type' => $post->pet->type,
        ] : null;

        // Repost Handling
        if ($post->repost) {
            $data['repost'] = [
                'id' => $post->repost->id,
                'content' => $post->repost->content,
                'featured_image' => $post->repost->featured_image,
                'featured_video' => $post->repost->featured_video,
                'images' => $post->repost->images,
                'videos' => $post->repost->videos,
                'feeling' => $post->repost->feeling,
                'activity' => $post->repost->activity,
                'user' => $slimUser($post->repost->user),
                'parent' => $slimUser($post->repost->parent),
                'pet' => $post->repost->pet ? [
                    'id' => $post->repost->pet->id,
                    'name' => $post->repost->pet->name,
                    'profile_pic' => $post->repost->pet->profile_pic,
                ] : null,
            ];
        } else {
            $data['repost'] = null;
        }

        if ($includeComments && $post->relationLoaded('comments')) {
            $data['comments'] = $post->comments->map(function ($comment) use ($slimUser) {
                return [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'commented_by_type' => $comment->commented_by_type,
                    'commented_by_id' => $comment->commented_by_id,
                    'user' => $comment->commented_by_type === 'parent' ? $slimUser($comment->user) : null,
                    'created_at' => $comment->created_at,
                ];
            });
        }

        return $data;
    }
}
