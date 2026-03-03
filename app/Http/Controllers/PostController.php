<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Share;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Pet;

use App\Models\User;

use App\Models\PostImage;
use App\Models\PostVideo;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Notification;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;


class PostController extends Controller
{
    
  protected $fcm;

    public function __construct()
    {
        $projectId = config('services.firebase.project_id');
        $credentialsPath = public_path(config('services.firebase.credentials_path'));
        
        $this->fcm = new FirebaseService($projectId, $credentialsPath);
    }    
    
  public function createPetBirthdayPosts()
  {
    $today = Carbon::now();

    $pets = Pet::whereDay('dob', $today->day)
        ->whereMonth('dob', $today->month)
        ->get();

    foreach ($pets as $pet) {
        
        $alreadyPosted = Post::where('pet_id', $pet->id)
            ->where('post_type', 'birthday')
            ->whereDate('created_at', $today)
            ->exists();

        if ($alreadyPosted) {
            continue; 
        }

        $content = "🎉 Happy Birthday {$pet->name}! 🎂\nWishing you a day full of love, treats, and cuddles from your parent! 🐾";

        // Create post directly
        $post = new Post();
        $post->parent_id = $pet->user_id;
        $post->pet_id = $pet->id;
        $post->post_type = 'birthday';
        $post->slug = Str::slug(Str::random(6) . '-' . now()->timestamp);
        $post->content = $content;
        $post->is_public = true;
        $post->save();
        
         $friends = DB::table('friend_requests')->where(function ($q) use ($pet) {
                $q->where('from_parent_id', $pet->user_id)
                  ->orWhere('to_parent_id', $pet->user_id);
            })->where('status', 'accepted')->get();
         foreach ($friends as $friend) {
            $friendId = $friend->from_parent_id == $pet->user_id
                ? $friend->to_parent_id
                : $friend->from_parent_id;

            // create notification
            $notification = Notification::create([
                'user_id'       => $friendId,          
                'sender_id'     => $pet->user_id,      
                'notifiable_id' => $post->id,         
                'type'          => 'birthday_post',
                'title'         => "🎂 {$pet->name}'s Birthday!",
                'message'       => "{$pet->name} is celebrating a birthday today! 🎉",
                'is_read'       => false,
            ]);

            
            $receiver = User::find($friendId);
            if ($receiver && $receiver->device_token) {
                try {
                    $this->fcm->sendNotification(
                        [$receiver->device_token],
                        [
                            'title' => "🎂 {$pet->name}'s Birthday!",
                            'body'  => "{$pet->name} is celebrating a birthday today! 🎉",
                            'sender_id' => (string) $pet->user_id,
                            'type' => 'birthday_post',
                            'notification_id' => $notification->id,
                            'notifiable_id' => $post->id
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error("Birthday notification failed: " . $e->getMessage());
                }
            }
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Birthday posts created successfully.',
        'count' => $pets->count(),
    ]);
  }

  public function storeOld(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
        'content' => 'nullable|string',
        'is_public' => 'nullable|boolean',
        'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'featured_video' => 'nullable|mimes:mp4,mov,avi|max:51200',
        'post_images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'post_videos.*' => 'required|mimes:mp4,mov,avi|max:51200',
        'tagged_user_ids' => 'nullable|array',
        'tagged_user_ids.*' => 'exists:users,id',
    ]);

    // dd($request->file('post_images'));
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 200);
    }

    $data = $validator->validated();

    $post = new Post();
    $post->parent_id = $data['parent_id'];
    $post->slug = Str::slug(Str::random(6) . '-' . now()->timestamp);
    $post->content = $data['content'] ?? null;
    $post->is_public = $data['is_public'] ?? true;
    $manager = new ImageManager(new Driver());
    
    if ($request->hasFile('featured_image')) {
        $file = $request->file('featured_image');
        $image = $manager->read($file);
        $filename = uniqid('featured_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/posts/' . $filename;
        // $image->resize(800, 800, function ($constraint) {
        //     $constraint->aspectRatio();
        //     $constraint->upsize();
        // })->save(public_path($path), 100); 
        // $image->resize(800, null, function ($constraint) {
        //     $constraint->aspectRatio();
        //     $constraint->upsize();
        //  })->save(public_path($path), 100);
        // $post->featured_image = $path;
        $image->save(public_path($path), 100);
        $post->featured_image = $path;
    }

    // Handle featured_video
    if ($request->hasFile('featured_video')) {
        $file = $request->file('featured_video');
        $filename = uniqid('featured_video_') . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('uploads/posts/videos'), $filename);
        $post->featured_video = 'uploads/posts/videos/' . $filename;
    }

    $post->save();
    
    
    
    // Handle multiple post_images
    if ($request->hasFile('post_images')) {
        foreach ($request->file('post_images') as $file) {
            $image = $manager->read($file);
            $filename = uniqid('post_image_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/posts/' . $filename;
        //     $image->resize(800, null, function ($constraint) {
        //     $constraint->aspectRatio();
        //     $constraint->upsize();
        //  })->save(public_path($path), 100);
            $image->save(public_path($path), 100);

            PostImage::create([
                'post_id' => $post->id,
                'image_path' => $path,
            ]);
        }
    }

    // Handle multiple post_videos
    if ($request->hasFile('post_videos')) {
        foreach ($request->file('post_videos') as $file) {
            $filename = uniqid('post_video_') . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path('uploads/posts/videos'), $filename);

            PostVideo::create([
                'post_id' => $post->id,
                'video_path' => 'uploads/posts/videos/' . $filename,
            ]);
        }
    }
    if (!empty($data['tagged_user_ids'])) {
    foreach ($data['tagged_user_ids'] as $taggedUserId) {
        DB::table('post_tags')->insert([
            'post_id' => $post->id,
            'tagged_user_id' => $taggedUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        DB::table('notifications')->insert([
            'user_id' => $taggedUserId,                      // recipient
            'sender_id' => $post->parent_id,                 // post creator
            'notifiable_id' => $post->id,                    // post id
            'type' => 'tagged_in_post',                      // custom type
            'message' => 'You were tagged in a post.',       // custom message
            'is_read' => 0,                                  // unread
            'created_at' => now(),
            'updated_at' => now(),
        ]);
     }
     
   }
   
    
    
   
   
    return response()->json([
        'status' => true,
        'message' => 'Post created by Parent!',
        'data' => $post->load(['images', 'videos','taggedUsers']),
    ], 201);
}


  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'parent_id' => 'required|exists:users,id',
        'content' => 'nullable|string',
        'is_public' => 'nullable|boolean',
        'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        'featured_video' => 'nullable|mimes:mp4,mov,avi',
        'post_images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        'post_videos.*' => 'required|mimes:mp4,mov,avi',
        'tagged_user_ids' => 'nullable|array',
        'tagged_user_ids.*' => 'exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 200);
    }

    $data = $validator->validated();

    $post = new Post();
    $post->parent_id = $data['parent_id'];
    $post->slug = Str::slug(Str::random(6) . '-' . now()->timestamp);
    $post->content = $data['content'] ?? null;
    $post->is_public = $data['is_public'] ?? true;
    $manager = new ImageManager(new Driver());
    
    if ($request->hasFile('featured_image')) {
        $file = $request->file('featured_image');
        $image = $manager->read($file);
        $filename = uniqid('featured_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/posts/' . $filename;
        $image->save(public_path($path), 100);
        $post->featured_image = $path;
    }

    // Handle featured_video
    if ($request->hasFile('featured_video')) {
        $file = $request->file('featured_video');
        $filename = uniqid('featured_video_') . '.' . $file->getClientOriginalExtension();
        $path = $file->move(public_path('uploads/posts/videos'), $filename);
        $post->featured_video = 'uploads/posts/videos/' . $filename;
    }

    $post->save();
    
    // Handle multiple post_images
    if ($request->hasFile('post_images')) {
        foreach ($request->file('post_images') as $file) {
            $image = $manager->read($file);
            $filename = uniqid('post_image_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/posts/' . $filename;
            $image->save(public_path($path), 100);

            PostImage::create([
                'post_id' => $post->id,
                'image_path' => $path,
            ]);
        }
    }

    // Handle multiple post_videos
    if ($request->hasFile('post_videos')) {
        foreach ($request->file('post_videos') as $file) {
            $filename = uniqid('post_video_') . '.' . $file->getClientOriginalExtension();
            $path = $file->move(public_path('uploads/posts/videos'), $filename);

            PostVideo::create([
                'post_id' => $post->id,
                'video_path' => 'uploads/posts/videos/' . $filename,
            ]);
        }
    }
    
    // Handle tagged users and send notifications
    if (!empty($data['tagged_user_ids'])) {
        foreach ($data['tagged_user_ids'] ?? [] as $taggedUserId) {
            DB::table('post_tags')->insert([
                'post_id' => $post->id,
                'tagged_user_id' => $taggedUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Create notification in database
            $notification = Notification::create([
                'user_id' => $taggedUserId,                      // recipient
                'sender_id' => $post->parent_id,                 // post creator
                'notifiable_id' => $post->id,                    // post id
                'type' => 'tagged_in_post',                      // custom type
                'title' => 'You were tagged in a post',          // notification title
                'message' => 'You were tagged in a post.',       // custom message
                'is_read' => false,                              // unread
            ]);
            
            // Send push notification to tagged user
            $taggedUser = User::find($taggedUserId);
            if ($taggedUser && $taggedUser->device_token) {
                try {
                    $this->fcm->sendNotification(
                        [$taggedUser->device_token],
                        [
                            'title' => 'You were tagged in a post',
                            'body'  => 'You were tagged in a post by ' . User::find($post->parent_id)->first_name ?? '',
                            'sender_id' => (string) $post->parent_id,
                            'type' => 'tagged_in_post',
                            'notification_id' => (string) $notification->id,
                            'notifiable_id' => (string) $post->id
                        ]
                    );
                } catch (\Exception $e) {
                    \Log::error("Tag notification failed: " . $e->getMessage());
                }
            }
        }
    }
    
    // Send notifications to friends about the new post
    $postCreator = User::find($post->parent_id);
    $friends = DB::table('friend_requests')->where(function ($q) use ($postCreator) {
            $q->where('from_parent_id', $postCreator->id)
              ->orWhere('to_parent_id', $postCreator->id);
        })->where('status', 'accepted')->get();
        
    foreach ($friends as $friend) {
        $friendId = $friend->from_parent_id == $postCreator->id
            ? $friend->to_parent_id
            : $friend->from_parent_id;

        // Skip if friend is the post creator
        if ($friendId == $postCreator->id) {
            continue;
        }

        // Create notification in database
        $c_name=$postCreator->first_name ?? '';
        $notification = Notification::create([
            'user_id'       => $friendId,          
            'sender_id'     => $postCreator->id,      
            'notifiable_id' => $post->id,         
            'type'          => 'new_post',
            'title'         => 'New post from ' . $c_name,
            'message'       => $c_name . ' shared a new post',
            'is_read'       => false,
        ]);

        // Send push notification
        $friendUser = User::find($friendId);
        if ($friendUser && $friendUser->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$friendUser->device_token],
                    [
                        'title' => 'New post from ' . $c_name,
                        'body'  => substr($post->content, 0, 100) . (strlen($post->content) > 100 ? '...' : ''),
                        'sender_id' => (string) $postCreator->id,
                        'type' => 'new_post',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $post->id
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("New post notification failed: " . $e->getMessage());
            }
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Post created by Parent!',
        'data' => $post->load(['images', 'videos','taggedUsers']),
    ], 201);
}

  public function repostOld(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id', 
        'post_id' => 'required|exists:posts,id', 
        'is_public' => 'nullable|boolean',
        'forward_to_user_id' => 'nullable|exists:users,id', 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 200);
    }

    $data = $validator->validated();

    $originalPost = Post::with(['parent','repost','repost.images', 'repost.videos'])->withCount(['likes', 'shares', 'comments'])->findOrFail($data['post_id']);

    $repost = new Post();
    $repost->parent_id = $data['user_id'];
    $repost->repost_id = $originalPost->id;
    $repost->post_type = 'repost';
    $repost->slug = Str::slug(Str::random(6) . '-' . now()->timestamp);
    $repost->content = $originalPost->content;
    $repost->is_public = $data['is_public'] ?? true;

    
    if (isset($data['forward_to_user_id'])) {
        $repost->forward_to_user_id = $data['forward_to_user_id']; 
    }

    $repost->save();

    $newPost = Post::with(['parent','repost'])->withCount(['likes', 'shares', 'comments'])->findOrFail($repost->id);
    return response()->json([
        'status' => true,
        'message' => 'Post forwarded successfully!',
        'data' => $newPost,
    ], 201);
}

public function repost(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id', 
        'post_id' => 'required|exists:posts,id', 
        'is_public' => 'nullable|boolean',
        'forward_to_user_id' => 'nullable|exists:users,id', 
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 200);
    }

    $data = $validator->validated();

    $originalPost = Post::with(['parent','repost','repost.images', 'repost.videos'])->withCount(['likes', 'shares', 'comments'])->findOrFail($data['post_id']);

    $repost = new Post();
    $repost->parent_id = $data['user_id'];
    $repost->repost_id = $originalPost->id;
    $repost->post_type = 'repost';
    $repost->slug = Str::slug(Str::random(6) . '-' . now()->timestamp);
    $repost->content = $originalPost->content;
    $repost->is_public = $data['is_public'] ?? true;

    if (isset($data['forward_to_user_id'])) {
        $repost->forward_to_user_id = $data['forward_to_user_id']; 
    }

    $repost->save();

    // Get the user who is reposting
    $repostUser = User::find($data['user_id']);
    
    $c_name=$repostUser->first_name ?? 'Unknown';
    
    // Notification for the original post creator
    if ($originalPost->parent_id != $data['user_id']) {
        $notification = Notification::create([
            'user_id'       => $originalPost->parent_id,
            'sender_id'     => $data['user_id'],
            'notifiable_id' => $repost->id,
            'type'          => 'repost',
            'title'         => 'Your post was shared',
            'message'       => $c_name . ' shared your post',
            'is_read'       => false,
        ]);

        // Send push notification to original post creator
        $originalPostUser = User::find($originalPost->parent_id);
        if ($originalPostUser && $originalPostUser->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$originalPostUser->device_token],
                    [
                        'title' => 'Your post was shared',
                        'body'  => $c_name . ' shared your post',
                        'sender_id' => (string) $data['user_id'],
                        'type' => 'repost',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $repost->id
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Repost notification failed for original creator: " . $e->getMessage());
            }
        }
    }

    // Notification for the user being forwarded to (if specified)
    if (isset($data['forward_to_user_id']) && $data['forward_to_user_id'] != $data['user_id']) {
        $notification = Notification::create([
            'user_id'       => $data['forward_to_user_id'],
            'sender_id'     => $data['user_id'],
            'notifiable_id' => $repost->id,
            'type'          => 'forwarded_post',
            'title'         => 'Post shared with you',
            'message'       => $c_name . ' shared a post with you',
            'is_read'       => false,
        ]);

        // Send push notification to the forwarded user
        $forwardedUser = User::find($data['forward_to_user_id']);
        if ($forwardedUser && $forwardedUser->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$forwardedUser->device_token],
                    [
                        'title' => 'Post shared with you',
                        'body'  => $c_name . ' shared a post with you',
                        'sender_id' => (string) $data['user_id'],
                        'type' => 'forwarded_post',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $repost->id
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Forward notification failed: " . $e->getMessage());
            }
        }
    }

    $newPost = Post::with(['parent','repost'])->withCount(['likes', 'shares', 'comments','reposts'])->findOrFail($repost->id);
    return response()->json([
        'status' => true,
        'message' => 'Post forwarded successfully!',
        'data' => $newPost,
    ], 201);
}


  public function deletePost(Request $request)
  {
    $validator = Validator::make($request->all(), [
        'post_id' => 'required|exists:posts,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 200);
    }

    $data = $validator->validated();

    $post = Post::with(['images', 'videos'])->find($data['post_id']);

    if ($post->featured_image && File::exists(public_path($post->featured_image))) {
        File::delete(public_path($post->featured_image));
    }

    if ($post->featured_video && File::exists(public_path($post->featured_video))) {
        File::delete(public_path($post->featured_video));
    }

    foreach ($post->images as $image) {
        if ($image->image_path && File::exists(public_path($image->image_path))) {
            File::delete(public_path($image->image_path));
        }
        $image->delete();
    }

    // Delete post videos
    foreach ($post->videos as $video) {
        if ($video->video_path && File::exists(public_path($video->video_path))) {
            File::delete(public_path($video->video_path));
        }
        $video->delete();
    }
    $post->delete();
    return response()->json([
        'status' => true,
        'message' => 'Post and associated media deleted successfully.',
    ], 200);
}

  public function updatePost(Request $request)
  {
    // 1) Validate
    $validator = Validator::make($request->all(), [
        'id'               => 'required|exists:posts,id',
        'parent_id'           => 'required|exists:users,id',
        'content'          => 'nullable|string',
        'is_public'        => 'nullable|boolean',
        'featured_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        'featured_video'   => 'nullable|mimes:mp4,mov,avi',
        'post_images.*'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        'post_videos.*'    => 'nullable|mimes:mp4,mov,avi',
        'tagged_user_ids' => 'nullable|array',
        'tagged_user_ids.*' => 'exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 200);
    }

    // 2) Fetch post
    $data    = $validator->validated();
    $post    = Post::with(['images','videos'])->find($data['id']);
    $manager = new ImageManager(new Driver());

    // 3) Update basic fields
    $post->parent_id = $data['parent_id'];
    $post->content   = $data['content']   ?? $post->content;
    $post->is_public = $data['is_public'] ?? $post->is_public;
    
    // 4) Featured Image
    if ($request->hasFile('featured_image')) {
        // delete old
        if ($post->featured_image && File::exists(public_path($post->featured_image))) {
            File::delete(public_path($post->featured_image));
        }
        // store new
        $file     = $request->file('featured_image');
        $image    = $manager->read($file);
        $filename = uniqid('featured_') .'.'.$file->getClientOriginalExtension();
        $path     = 'uploads/posts/'.$filename;
    //   $image->resize(800, null, function ($constraint) {
    //         $constraint->aspectRatio();
    //         $constraint->upsize();
    //      })->save(public_path($path), 100);
    //     $post->featured_image = $path;
       $image->save(public_path($path), 100);
       $post->featured_image = $path;
    }

    // 5) Featured Video
    if ($request->hasFile('featured_video')) {
        if ($post->featured_video && File::exists(public_path($post->featured_video))) {
            File::delete(public_path($post->featured_video));
        }
        $file     = $request->file('featured_video');
        $filename = uniqid('featured_video_') .'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/posts/videos'), $filename);
        $post->featured_video = 'uploads/posts/videos/'.$filename;
    }

    $post->save();

    // 6) Replace post_images if any new uploaded
    if ($request->hasFile('post_images')) {
        // delete old files & records
        foreach ($post->images as $img) {
            if (File::exists(public_path($img->image_path))) {
                File::delete(public_path($img->image_path));
            }
            $img->delete();
        }
        // save new ones
        foreach ($request->file('post_images') as $file) {
            $image    = $manager->read($file);
            $filename = uniqid('post_image_') .'.'.$file->getClientOriginalExtension();
            $path     = 'uploads/posts/'.$filename;
            $image->save(public_path($path), 100);

            PostImage::create([
                'post_id'    => $post->id,
                'image_path' => $path,
            ]);
        }
    }

    // 7) Replace post_videos if any new uploaded
    if ($request->hasFile('post_videos')) {
        foreach ($post->videos as $vid) {
            if (File::exists(public_path($vid->video_path))) {
                File::delete(public_path($vid->video_path));
            }
            $vid->delete();
        }
        foreach ($request->file('post_videos') as $file) {
            $filename = uniqid('post_video_') .'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/posts/videos'), $filename);
            PostVideo::create([
                'post_id'    => $post->id,
                'video_path' => 'uploads/posts/videos/'.$filename,
            ]);
        }
    }

   if (isset($data['tagged_user_ids'])) {
        $post->taggedUsers()->sync($data['tagged_user_ids']);
        foreach ($data['tagged_user_ids'] as $taggedUserId) {
            if ($taggedUserId != $post->parent_id) { 
                 DB::table('notifications')->insert([
                    'user_id'    => $taggedUserId,
                    'sender_id'  => $post->parent_id,
                    'notifiable_id' => $post->id,                 
                    'type' => 'tagged_in_post',   
                    'message'    => "You were tagged in a post. That post is update by creator",
                    'is_read'    => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    return response()->json([
        'status'  => true,
        'message' => 'Post updated successfully!',
        'data'    => $post->fresh()->load(['images','videos']),
    ], 200);
}

  public function getMyPosts(Request $request)
  {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }
        
        $userId=$request->parent_id;
        
        $posts = Post::where('parent_id', $request->parent_id)
                 ->whereNotIn('id', function ($query) use ($userId) {
                  $query->select('post_id')
                      ->from('post_hides')
                      ->where('user_id', $userId);
             })
            ->with(['images', 'videos','parent','repost','repost.images', 'repost.videos','taggedUsers','pet'])
            ->withCount(['likes', 'shares', 'comments','reposts'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate(10);
       $posts->getCollection()->transform(function ($post) {
           $post->created_at_human = $post->created_at->diffForHumans();
           return $post;
        });
        return response()->json([
            'status' => true,
            'data'   => $posts,            
        ], 200);
    }

  public function getAllPosts(Request $request)
  {
       
        $posts = Post::with(['images', 'videos','parent','repost','repost.images', 'repost.videos','taggedUsers','pet'])
            ->withCount(['likes', 'shares', 'comments','reposts'])
            ->orderBy('created_at', 'desc')
            ->simplePaginate(20);
        $posts->getCollection()->transform(function ($post) {
           $post->created_at_human = $post->created_at->diffForHumans();
           return $post;
        });
        return response()->json([
            'status' => true,
            'data'   => $posts,
        ], 200);
    }
    
    
  public function likeOld(Request $request)
  {
        $v = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'parent_id'  => 'required|exists:users,id',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }

        $postId = $request->post_id;
        $parentId  = $request->parent_id;

        if (Like::where('post_id', $postId)->where('parent_id', $parentId)->exists()) {
            Like::where('post_id', $postId)->where('parent_id', $parentId)->first()->delete();
            return response()->json([
                'status'  => true,
                'message' => 'Disliked the post',
            ], 200);
        }

        Like::create(['post_id'=>$postId,'parent_id'=>$parentId]);

        $count = Like::where('post_id', $postId)->count();

        return response()->json([
            'status'      => true,
            'message'     => 'Post liked',
            'likes_count' => $count,
        ], 201);
    }

public function like(Request $request)
{
    $v = Validator::make($request->all(), [
        'post_id' => 'required|exists:posts,id',
        'parent_id'  => 'required|exists:users,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $v->errors()->first(),
            'errors'  => $v->errors(),
        ], 422);
    }

    $postId = $request->post_id;
    $parentId = $request->parent_id;
    $post = Post::findOrFail($postId);

    if (Like::where('post_id', $postId)->where('parent_id', $parentId)->exists()) {
        Like::where('post_id', $postId)->where('parent_id', $parentId)->first()->delete();
        
        return response()->json([
            'status'  => true,
            'message' => 'Disliked the post',
        ], 200);
    }

    Like::create(['post_id'=>$postId,'parent_id'=>$parentId]);
    $count = Like::where('post_id', $postId)->count();

    // Send notification to post owner (if not liking own post)
    if ($post->parent_id != $parentId) {
        $likingUser = User::find($parentId);
        $c_name = $likingUser->first_name ?? 'Unknown';
        // Create notification in database
        $notification = Notification::create([
            'user_id'       => $post->parent_id,
            'sender_id'     => $parentId,
            'notifiable_id' => $postId,
            'type'          => 'post_like',
            'title'         => 'New like on your post',
            'message'       => $c_name . ' liked your post',
            'is_read'       => false,
        ]);

        // Send push notification to post owner
        $postOwner = User::find($post->parent_id);
        if ($postOwner && $postOwner->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$postOwner->device_token],
                    [
                        'title' => 'New like on your post',
                        'body'  => $c_name. ' liked your post',
                        'sender_id' => (string) $parentId,
                        'type' => 'post_like',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $postId
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Like notification failed: " . $e->getMessage());
            }
        }
    }

    return response()->json([
        'status'      => true,
        'message'     => 'Post liked',
        'likes_count' => $count,
    ], 201);
}

  public function shareOld(Request $request)
  {
        $v = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'parent_id'  => 'required|exists:users,id',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }

        $postId = $request->post_id;
        $parentId  = $request->parent_id;

        if (Share::where('post_id', $postId)->where('parent_id', $parentId)->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'Already shared',
            ], 200);
        }

        Share::create(['post_id'=>$postId,'parent_id'=>$parentId]);

        $count = Share::where('post_id', $postId)->count();

        return response()->json([
            'status'       => true,
            'message'      => 'Post shared',
            'shares_count' => $count,
        ], 201);
    }

public function share(Request $request)
{
    $v = Validator::make($request->all(), [
        'post_id' => 'required|exists:posts,id',
        'parent_id'  => 'required|exists:users,id',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $v->errors()->first(),
            'errors'  => $v->errors(),
        ], 422);
    }

    $postId = $request->post_id;
    $parentId = $request->parent_id;
    $post = Post::findOrFail($postId);

    if (Share::where('post_id', $postId)->where('parent_id', $parentId)->exists()) {
        return response()->json([
            'status'  => false,
            'message' => 'Already shared',
        ], 200);
    }

    Share::create(['post_id'=>$postId,'parent_id'=>$parentId]);
    $count = Share::where('post_id', $postId)->count();

    // Send notification to post owner (if not sharing own post)
    if ($post->parent_id != $parentId) {
        $sharingUser = User::find($parentId);
        $cname=$sharingUser->first_name ?? '';
        // Create notification in database
        $notification = Notification::create([
            'user_id'       => $post->parent_id,
            'sender_id'     => $parentId,
            'notifiable_id' => $postId,
            'type'          => 'post_share',
            'title'         => 'Your post was shared',
            'message'       => $cname . ' shared your post',
            'is_read'       => false,
        ]);

        // Send push notification to post owner
        $postOwner = User::find($post->parent_id);
        if ($postOwner && $postOwner->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$postOwner->device_token],
                    [
                        'title' => 'Your post was shared',
                        'body'  => $cname . ' shared your post',
                        'sender_id' => (string) $parentId,
                        'type' => 'post_share',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $postId
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Share notification failed: " . $e->getMessage());
            }
        }
    }

    return response()->json([
        'status'       => true,
        'message'      => 'Post shared',
        'shares_count' => $count,
    ], 201);
}

  public function commentOld(Request $request)
  {
        $v = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
            'parent_id'  => 'required|exists:users,id',
            'comment' => 'required|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }

        $c = Comment::create([
            'post_id' => $request->post_id,
            'parent_id'  => $request->parent_id,
            'comment' => $request->comment,
        ]);

        $count = Comment::where('post_id', $request->post_id)->count();

        return response()->json([
            'status'         => true,
            'message'        => 'Comment added',
            'comment'        => $c,
            'comments_count' => $count,
        ], 201);
    }   
    
    public function comment(Request $request)
{
    $v = Validator::make($request->all(), [
        'post_id' => 'required|exists:posts,id',
        'parent_id'  => 'required|exists:users,id',
        'comment' => 'required|string',
    ]);

    if ($v->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $v->errors()->first(),
            'errors'  => $v->errors(),
        ], 422);
    }

    $postId = $request->post_id;
    $parentId = $request->parent_id;
    $post = Post::findOrFail($postId);

    $c = Comment::create([
        'post_id' => $postId,
        'parent_id'  => $parentId,
        'comment' => $request->comment,
    ]);

    $count = Comment::where('post_id', $postId)->count();


    if ($post->parent_id != $parentId) {
        $commentingUser = User::find($parentId);
        $commentPreview = strlen($request->comment) > 50 ? substr($request->comment, 0, 50) . '...' : $request->comment;
        $cname = $commentingUser->first_name ?? 'Unknown';
       
        $notification = Notification::create([
            'user_id'       => $post->parent_id,
            'sender_id'     => $parentId,
            'notifiable_id' => $postId,
            'type'          => 'post_comment',
            'title'         => 'New comment on your post',
            'message'       => $cname . ' commented: ' . $commentPreview,
            'is_read'       => false,
        ]);
        $postOwner = User::find($post->parent_id);
        if ($postOwner && $postOwner->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$postOwner->device_token],
                    [
                        'title' => 'New comment on your post',
                        'body'  => $cname . ' commented: ' . $commentPreview,
                        'sender_id' => (string) $parentId,
                        'type' => 'post_comment',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $postId
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Comment notification failed: " . $e->getMessage());
            }
        }
    }

    return response()->json([
        'status'         => true,
        'message'        => 'Comment added',
        'comment'        => $c,
        'comments_count' => $count,
    ], 201);
}
    
  public function getComment(Request $request)
  {
        $v = Validator::make($request->all(), [
            'post_id' => 'required|exists:posts,id',
        ]);
        if ($v->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $v->errors()->first(),
                'errors'  => $v->errors(),
            ], 422);
        }
   $comments = Comment::where('post_id', $request->post_id)->with('user')->get()->map(function ($comment) {
       $comment->human_date = Carbon::parse($comment->created_at)->diffForHumans();
       return $comment;
   });
        return response()->json([
            'status'         => true,
            'message'        => 'Comment fetched for this post',
            'comment'        => $comments,
        ], 200);
    }    
  
  public function search(Request $request)
  {
    $query = Post::query();
    if ($request->has('keyword') && $request->filled('keyword')) {
        $keyword = $request->input('keyword');

        $query->where(function ($q) use ($keyword) {
            $q->where('content', 'LIKE', "%{$keyword}%")
              ->orWhereHas('parent', function ($q2) use ($keyword) {
                  $q2->where('name', 'LIKE', "%{$keyword}%");
              });
        });
      
        if ($request->has('user_id')) {
        $userId = $request->input('user_id');
        $query->whereNotIn('id', function ($sub) use ($userId) {
            $sub->select('post_id')
                ->from('post_hides')
                ->where('user_id', $userId);
        });
     }

       $posts = $query->with(['parent', 'images', 'videos','taggedUsers'])
                      ->withCount(['likes', 'shares', 'comments','reposts'])
                      ->orderBy('created_at', 'desc')
                      ->simplePaginate(10);

        return response()->json([
        'status' => true,
        'message' => 'Search results',
        'data' => $posts,
    ]);
    }
    return response()->json([
        'status' => true,
        'message' => 'Search results',
        'data' => [],
    ]);
  }  


//   admin dashboard post funtionality here

    public function index()
    {
        $posts = Post::with([
            'pet',
            'parent',
            'images',
            'likes',
            'comments'
        ])->where('deleted_at', 1) ->latest()->get();
        
        // echo "<pre>"; print_r($posts->toArray()); exit;
        return view('admin.post.index', compact('posts'));
    }

    public function create()
    {
        $users = User::all();
        return view('admin.post.create' , compact('users'));
    }

    public function getPetsByUser($userId) 
    {
        $pets = Pet::where('user_id', $userId)->select('id', 'name')->get();
        return response()->json($pets);
     }


    public function show($id)
    {
        $post = Post::with([
            'pet',
            'parent',
            'images',
            'videos',
            'likes',
            'comments',
            'taggedUsers'
        ])->findOrFail($id);
        
        return view('admin.post.show', compact('post'));
    }

    public function edit($id)
    {
        $users = User::all();
        $post = Post::with(['pet', 'parent', 'images', 'videos'])->findOrFail($id);
        $pets = Pet::where('user_id', $post->parent_id)->select('id', 'name')->get();
        
        return view('admin.post.edit', compact('post', 'users', 'pets'));
    }

    

   public function delete($id)
    {
        $post = Post::findOrFail($id);
        $post->update([
            'deleted_at' => 0 
        ]);
        
        return redirect()->route('admin.post.index')->with('success', 'Post deleted successfully.');
    }




 

public function save_post(Request $request)
{
    // ✅ Step 1: Validate input (no slug field now)
    $validatedData = $request->validate([
        'user_id' => 'required|exists:users,id',
        'pet_id' => 'required|exists:pets,id',
        'type' => 'required|in:normal,birthday,repost',
        'description' => 'required|string',
        'images' => 'required|array|min:1|max:10',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'video' => 'nullable|file|mimes:mp4,3gp,mov,avi|max:20480',
    ]);

    // ✅ Step 2: Generate unique slug automatically
    $slug = Str::slug(Str::random(6) . '-' . now()->timestamp);

    // ✅ Step 3: Save post
    $post = Post::create([
        'parent_id' => $validatedData['user_id'],
        'slug' => $slug,
        'content' => $validatedData['description'],
        'is_public' => true,
        'pet_id' => $validatedData['pet_id'],
        'post_type' => $validatedData['type'],
    ]);

       if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $filename = uniqid('post_image_') . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/posts');
                $file->move($destination, $filename);

                PostImage::create([
                    'post_id' => $post->id,
                    'image_path' => 'uploads/posts/' . $filename,
                    'display_order' => $index + 1,
                ]);
            }
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $filename = uniqid('post_video_') . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/posts/videos');
            $file->move($destination, $filename);

            PostVideo::create([
                'post_id' => $post->id,
                'video_path' => 'uploads/posts/videos/' . $filename,
                'display_order' => 1,
            ]);
        }

    // ✅ Step 6: Redirect with success message
    return redirect()->route('admin.post.index')
        ->with('success', 'Post created successfully!');
}

public function toggleStatus($id, Request $request)
{
    $post = Post::findOrFail($id);
    
    // Toggle status
    $post->is_public = $post->is_public ? 0 : 1;
    $post->save();

    return response()->json([
        'success' => true,
        'new_status' => $post->is_public
    ]);
}

public function gallery($id)
{
    $post = Post::with(['images', 'videos'])->findOrFail($id);
    
    // Return HTML for the gallery
    return view('admin.post.gallery', compact('post'));
}

// PostController.php
public function deleteImage($id)
{
    try {
        $media = PostImage::findOrFail($id);
        
        // Delete file from storage
        if (file_exists(public_path($media->image_path))) {
            unlink(public_path($media->image_path));
        }
        
        $media->delete();
        
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}

public function deleteVideo($id)
{
    try {
        $media = PostVideo::findOrFail($id);
        
        // Delete file from storage
        if (file_exists(public_path($media->video_path))) {
            unlink(public_path($media->video_path));
        }
        
        $media->delete();
        
        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()]);
    }
}
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'pet_id' => 'required|exists:pets,id',
            'type' => 'required|in:normal,birthday,repost',
            'description' => 'required|string',
            'images' => 'nullable|array|min:0|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|file|mimes:mp4,3gp,mov,avi|max:20480',
        ]);

        // ✅ Step 3: Update post details
        $post->update([
            'parent_id' => $validatedData['user_id'],
            'pet_id' => $validatedData['pet_id'],
            'content' => $validatedData['description'],
            'post_type' => $validatedData['type'],
        ]);

    // ✅ Step 4: Handle Images (Keep old if not replaced)
        if ($request->hasFile('images')) {
            $existingCount = PostImage::where('post_id', $post->id)->count();
            $newImages = $request->file('images');

            foreach ($newImages as $index => $file) {
                $filename = uniqid('post_image_') . '.' . $file->getClientOriginalExtension();
                $destination = public_path('uploads/posts');
                $file->move($destination, $filename);

                PostImage::create([
                    'post_id' => $post->id,
                    'image_path' => 'uploads/posts/' . $filename,
                    'display_order' => $existingCount + $index + 1,
                ]);
            }
        }

    if ($request->hasFile('video')) {
        $existingVideo = PostVideo::where('post_id', $post->id)->first();

        if (!$existingVideo) {
            $file = $request->file('video');
            $filename = uniqid('post_video_') . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/posts/videos');
            $file->move($destination, $filename);

            PostVideo::create([
                'post_id' => $post->id,
                'video_path' => 'uploads/posts/videos/' . $filename,
                'display_order' => 1,
            ]);
        }
    }

    // ✅ Step 6: Redirect back with success message
    return redirect()->route('admin.post.index')
        ->with('success', 'Post updated successfully!');
}

    public function post_logs()
    {
        $posts = Post::with([
            'pet',
            'parent',
            'images',
            'likes',
            'comments'
        ])->where('deleted_at', 0) ->latest()->get();  // Only deleted posts
        
        // echo "<pre>"; print_r($posts->toArray()); exit;
        return view('admin.post.post_logs', compact('posts'));
    }

    public function restore_post(Post $post)
    {
        try {
            $post->update([
                'deleted_at' => 1 // Restore post
            ]);

            Log::info('Post restored', ['post_id' => $post->id]);

            return redirect()->route('admin.post.history-log')->with('success', 'Post restored successfully.');

        } catch (\Exception $e) {
            Log::error('Post restoration failed', [
                'error' => $e->getMessage(),
                'post_id' => $post->id
            ]);

            return redirect()->back()->with('error', 'Failed to restore post: ' . $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        try {
            $post = Post::findOrFail($id);

            // Delete associated images
            if ($post->images && count($post->images) > 0) {
                foreach ($post->images as $image) {
                    if (file_exists(public_path($image->image_path))) {
                        unlink(public_path($image->image_path));
                    }
                }
                $post->images()->delete();
            }

            // Delete associated videos
            if ($post->videos && count($post->videos) > 0) {
                foreach ($post->videos as $video) {
                    if (file_exists(public_path($video->video_path))) {
                        unlink(public_path($video->video_path));
                    }
                }
                $post->videos()->delete();
            }

            // Delete likes and comments
            $post->likes()->delete();
            $post->comments()->delete();

            // Permanently delete the post
            $post->delete();

            Log::info('Post permanently deleted', ['post_id' => $id]);

            return redirect()->route('admin.post.history-log')->with('success', 'Post permanently deleted.');

        } catch (\Exception $e) {
            Log::error('Post permanent deletion failed', [
                'error' => $e->getMessage(),
                'post_id' => $id
            ]);

            return redirect()->back()->with('error', 'Failed to delete post: ' . $e->getMessage());
        }
    }
    public function showDetails($id)
    {
        try {
            $post = Post::with([
                'pet',
                'parent',
                'images',
                'videos',
                'likes',
                'comments'
            ])->findOrFail($id);

            return view('admin.post.post_details', compact('post'));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Post not found'], 404);
        }
    }




    
    
}
