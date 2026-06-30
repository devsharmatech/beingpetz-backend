<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\V2Post;
use App\Models\V2\V2Comment;
use App\Models\V2\CommentLike;
use App\Services\V2\ValidationService;
use App\Services\V2\ContentModerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Notification;
use App\Services\FirebaseService;


class CommentController extends Controller
{
    
    protected $fcm;

public function __construct()
{
    $projectId = config('services.firebase.project_id');
    $credentialsPath = public_path(config('services.firebase.credentials_path'));

    $this->fcm = new FirebaseService(
        $projectId,
        $credentialsPath
    );
}
    /**
     * Store comment or reply
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'post_id' => 'required|integer|exists:posts,id',
                'comment' => 'required|string|max:2000',
                'commented_by_type' => 'required|in:parent,pet',
                'commented_by_id' => 'required|integer',

                'reply_to_comment_id' => 'nullable|integer|exists:comments,id',
                'reply_to_user_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Check post
            $post = V2Post::where('id', $validated['post_id'])
                ->where('status', 'active')
                ->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found or inactive'
                ], 404);
            }

            // Ownership validation
            // $ownershipValidation = ValidationService::validatePostedBy(
            //     $user->id,
            //     $validated['commented_by_type'],
            //     $validated['commented_by_id']
            // );

            // if (!$ownershipValidation['valid']) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => $ownershipValidation['message']
            //     ], 403);
            // }

            // Moderation
            $moderationResult = ContentModerationService::validate(
                $validated['comment'],
                null,
                $user->id,
                'comment',
                $request->ip(),
                $request->userAgent()
            );

            if ($moderationResult['action'] === 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment violates guidelines',
                    'data' => [
                        'violation_type' => $moderationResult['violation_type'],
                        'reason' => $moderationResult['reason'] ?? ''
                    ]
                ], 422);
            }

            // Sanitize
            $sanitized = ValidationService::sanitizeInput([
                'comment' => $validated['comment'],
            ]);

            // Expanded model (NO create())
            $comment = new V2Comment();

            $comment->post_id = $validated['post_id'];
            $comment->commented_by_type = $validated['commented_by_type'];
            $comment->commented_by_id = $validated['commented_by_id'];
            $comment->parent_id = $request->user()->id ?? null;

            $comment->is_reply = !empty($validated['reply_to_comment_id']);
            $comment->reply_to_comment_id = $validated['reply_to_comment_id'] ?? null;
            $comment->reply_to_user_id = $validated['reply_to_user_id'] ?? null;

            $comment->comment = $sanitized['comment'];
            $comment->status = 'active';
            $comment->moderation_reason = null;

            $comment->save();

/*
|--------------------------------------------------------------------------
| Notify Post Owner
|--------------------------------------------------------------------------
*/
if (!$comment->is_reply) {

    // New comment on post

    if ($post->parent_id != $user->id) {

        $commentPreview = strlen($comment->comment) > 50
            ? substr($comment->comment, 0, 50) . '...'
            : $comment->comment;

        $c_name = $user->first_name ?? 'Unknown';

        $notification = Notification::create([
            'user_id'       => $post->parent_id,
            'sender_id'     => $user->id,
            'notifiable_id' => $post->id,
            'type'          => 'post_comment',
            'title'         => 'New comment on your post',
            'message'       => $c_name . ' commented: ' . $commentPreview,
            'is_read'       => false,
        ]);

        $postOwner = User::find($post->parent_id);

        if ($postOwner && !empty($postOwner->device_token)) {

            try {

                $this->fcm->sendNotification(
                    [$postOwner->device_token],
                    [
                        'title' => 'New comment on your post',
                        'body'  => $c_name . ' commented: ' . $commentPreview,
                        'sender_id' => (string) $user->id,
                        'type' => 'post_comment',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $post->id,
                    ]
                );

            } catch (\Exception $e) {

                \Log::error(
                    'Comment notification failed: ' .
                    $e->getMessage()
                );
            }
        }
    }

} else {

    /*
    |--------------------------------------------------------------------------
    | Reply Notification
    |--------------------------------------------------------------------------
    */

    if (!empty($comment->reply_to_user_id)
        && $comment->reply_to_user_id != $user->id) {

        $commentPreview = strlen($comment->comment) > 50
            ? substr($comment->comment, 0, 50) . '...'
            : $comment->comment;

        $c_name = $user->first_name ?? 'Unknown';

        $notification = Notification::create([
            'user_id'       => $comment->reply_to_user_id,
            'sender_id'     => $user->id,
            'notifiable_id' => $comment->id,
            'type'          => 'comment_reply',
            'title'         => 'New reply to your comment',
            'message'       => $c_name . ' replied: ' . $commentPreview,
            'is_read'       => false,
        ]);

        $replyUser = User::find($comment->reply_to_user_id);

        if ($replyUser && !empty($replyUser->device_token)) {

            try {

                $this->fcm->sendNotification(
                    [$replyUser->device_token],
                    [
                        'title' => 'New reply to your comment',
                        'body'  => $c_name . ' replied: ' . $commentPreview,
                        'sender_id' => (string) $user->id,
                        'type' => 'comment_reply',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $comment->id,
                    ]
                );

            } catch (\Exception $e) {

                \Log::error(
                    'Reply notification failed: ' .
                    $e->getMessage()
                );
            }
        }
    }
}

            // Log flagged content
            if ($moderationResult['action'] === 'flagged') {
                ContentModerationService::logModeration(
                    $user->id,
                    'comment',
                    $comment->id,
                    $validated['comment'],
                    $moderationResult,
                    $request->ip(),
                    $request->userAgent()
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment added successfully',
                'data' => $this->formatCommentResponse($comment)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show single comment
     */
    public function show($id)
    {
        try {
            $comment = V2Comment::where('status', 'active')->find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatCommentResponse($comment)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update comment
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $comment = V2Comment::find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found'
                ], 404);
            }

            // Ownership check
            
            if ($comment->parent_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'comment' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Moderation
            $moderationResult = ContentModerationService::validate(
                $validated['comment'],
                null,
                $user->id,
                'comment',
                $request->ip(),
                $request->userAgent()
            );

            if ($moderationResult['action'] === 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment violates guidelines'
                ], 422);
            }

            $sanitized = ValidationService::sanitizeInput([
                'comment' => $validated['comment'],
            ]);

            $comment->comment = $sanitized['comment'];
            $comment->save();

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully',
                'data' => $this->formatCommentResponse($comment->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete comment
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $comment = V2Comment::find($id);

            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comment not found'
                ], 404);
            }

            // Ownership check
            if ($comment->commented_by_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format response
     */
    protected function formatCommentResponse(V2Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,

            'commented_by_type' => $comment->commented_by_type,
            'commented_by_id' => $comment->commented_by_id,
            'parent_id' => $comment->commented_by_id,

            'is_reply' => (bool) $comment->is_reply,
            'reply_to_comment_id' => $comment->reply_to_comment_id,
            'reply_to_user_id' => $comment->reply_to_user_id,

            'comment' => $comment->comment,
            'status' => $comment->status,
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
        ];
    }
    
public function getPostComments_(Request $request, $postId)
{
    try {
        $user = $request->user();

        $comments = V2Comment::where('post_id', $postId)
            ->where('is_reply', false)
            ->where('status', 'active')

            // 🔥 Priority: user's comments first
            ->orderByRaw("
                CASE 
                    WHEN commented_by_id = ? AND commented_by_type = 'parent' THEN 0
                    ELSE 1
                END
            ", [$user->id])

            ->orderBy('created_at', 'desc')

            // 🔥 Load replies + user/pet data
            ->with([
                'replies' => function ($q) {
                    $q->where('status', 'active')
                      ->orderBy('created_at', 'asc');
                },
                'commentedBy', // 👈 IMPORTANT
                'commentor', // 👈 IMPORTANT
                'replies.commentor', // 👈 IMPORTANT
                'replies.commentedBy' // 👈 IMPORTANT
            ])

            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments->map(function ($comment) {
                return $this->formatCommentWithReplies($comment);
            })
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch comments.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function getPostComments(Request $request, $postId)
{
    try {

        $user = $request->user();

        $comments = V2Comment::where('post_id', $postId)
            ->where('is_reply', false)
            ->where('status', 'active')

            // 🔥 User comments first
            ->orderByRaw("
                CASE 
                    WHEN commented_by_id = ? AND commented_by_type = 'parent' THEN 0
                    ELSE 1
                END
            ", [$user->id])

            ->orderBy('created_at', 'desc')

            // 🔥 Like Count
            ->withCount('likes')

            // 🔥 Check logged-in user liked or not
            ->withExists([
                'likes as is_liked' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                }
            ])

            // 🔥 Relations
            ->with([
                'replies' => function ($q) use ($user) {

                    $q->where('status', 'active')
                        ->orderBy('created_at', 'asc')
                        ->withCount('likes')
                        ->withExists([
                            'likes as is_liked' => function ($subQ) use ($user) {
                                $subQ->where('user_id', $user->id);
                            }
                        ]);
                },

                'commentedBy',
                'commentor',
                'replies.commentor',
                'replies.commentedBy'
            ])

            ->get();

        return response()->json([
            'success' => true,
            'data' => $comments->map(function ($comment) {
                return $this->formatCommentWithReplies($comment);
            })
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch comments.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

protected function formatCommentWithReplies($comment)
{
    return [
        'id' => $comment->id,
        'post_id' => $comment->post_id,

        'commented_by_type' => $comment->commented_by_type,
        'commented_by_id' => $comment->commented_by_id,
        'parent_id' => $comment->parent_id,
        'likes_count' => $comment->likes_count ?? 0,
        'is_liked' => (bool) ($comment->is_liked ?? false),

        // 👇 ADD THIS (important for frontend)
        'commented_by' => $comment->commentedBy ? [
            'id' => $comment->commentedBy->id,
            'username' => $comment->commentedBy->username ?? null,
            'first_name' => $comment->commentedBy->first_name ?? null,
            'last_name' => $comment->commentedBy->last_name ?? null,
            'profile' => $comment->commentedBy->profile ?? null,
        ] : null,
        'commentor' => $comment->commentor ? [
            'id' => $comment->commentor->id,
            'username' => $comment->commentor->username ?? null,
            'first_name' => $comment->commentor->first_name ?? null,
            'last_name' => $comment->commentor->last_name ?? null,
            'profile' => $comment->commentor->profile ?? null,
        ] : null,

        'comment' => $comment->comment,
        'created_at' => $comment->created_at,

        'replies' => $comment->replies->map(function ($reply) {
            return [
                'id' => $reply->id,
                'commented_by_type' => $reply->commented_by_type,
                'commented_by_id' => $reply->commented_by_id,
                'likes_count' => $reply->likes_count ?? 0,
                'is_liked' => (bool) ($reply->is_liked ?? false),

                'commented_by' => $reply->commentedBy ? [
                    'id' => $reply->commentedBy->id,
                    'username' => $comment->commentedBy->username ?? null,
            'first_name' => $comment->commentedBy->first_name ?? null,
            'last_name' => $comment->commentedBy->last_name ?? null,
            'profile' => $comment->commentedBy->profile ?? null,
                ] : null,

                'reply_to_user_id' => $reply->reply_to_user_id,
                'comment' => $reply->comment,
                'created_at' => $reply->created_at,
            ];
        }),
    ];
}

public function likeUnlike(Request $request)
{
    $validator = Validator::make($request->all(), [
        'comment_id' => 'required|exists:comments,id',
        'user_id'    => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $comment = V2Comment::find($request->comment_id);

    $alreadyLiked = CommentLike::where('comment_id', $request->comment_id)
        ->where('user_id', $request->user_id)
        ->first();

    // Remove Like
    if ($alreadyLiked) {

        $alreadyLiked->delete();

        return response()->json([
            'status' => true,
            'message' => 'Comment unliked successfully',
            'liked' => false,
            'total_likes' => CommentLike::where('comment_id', $request->comment_id)->count(),
        ]);
    }

    // Add Like
    CommentLike::create([
        'comment_id' => $request->comment_id,
        'user_id' => $request->user_id,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Comment liked successfully',
        'liked' => true,
        'total_likes' => CommentLike::where('comment_id', $request->comment_id)->count(),
    ]);
}
    
public function commentLikedUsers($commentId)
{
    try {

        $users = CommentLike::where('comment_id', $commentId)
            ->with([
                'user:id,first_name,last_name,username,profile'
            ])
            ->latest()
            ->get()
            ->map(function ($like) {

                return [
                    'id' => optional($like->user)->id,
                    'first_name' => optional($like->user)->first_name,
                    'last_name' => optional($like->user)->last_name,
                    'username' => optional($like->user)->username,
                    'profile' => optional($like->user)->profile,
                ];
            });

        return response()->json([
            'success' => true,
            'total' => $users->count(),
            'data' => $users,
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch liked users.',
            'error' => $e->getMessage(),
        ], 500);
    }
}    
    
}