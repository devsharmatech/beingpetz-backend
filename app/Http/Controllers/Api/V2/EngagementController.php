<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\V2Post;
use App\Models\V2\V2EngagementLike;
use App\Models\V2\V2EngagementShare;
use App\Services\V2\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EngagementController extends Controller
{
    /**
     * Get paginated likes for a post.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLikes(Request $request, $id)
    {
        try {
            $post = V2Post::where('id', $id)->where('status', 'active')->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $likes = V2EngagementLike::with(['user:id,first_name,last_name,username,profile', 'likedBy'])
                ->where('post_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'likes' => $likes->map(function ($like) {
                        return $this->formatLikeResponse($like);
                    }),
                    'pagination' => [
                        'current_page' => $likes->currentPage(),
                        'last_page' => $likes->lastPage(),
                        'per_page' => $likes->perPage(),
                        'total' => $likes->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve likes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getWhoRepost(Request $request, $id)
    {
        try {
            $post = V2Post::where('id', $id)->where('status', 'active')->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $perPage = $request->input('per_page', 50);
            $page = $request->input('page', 1);

            $reposts = V2Post::with(['user:id,first_name,last_name,username,profile'])
                ->where('repost_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $reposts->map(function ($repost) {
                        return $this->formatRepostResponse($repost);
                    }),
                    'pagination' => [
                        'current_page' => $reposts->currentPage(),
                        'last_page' => $reposts->lastPage(),
                        'per_page' => $reposts->perPage(),
                        'total' => $reposts->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve likes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated comments for a post.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComments(Request $request, $id)
    {
        try {
            $post = V2Post::where('id', $id)->where('status', 'active')->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $comments = $post->comments()
                ->with(['user:id,first_name,last_name,username,profile'])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'comments' => $comments->map(function ($comment) {
                        return $this->formatCommentResponse($comment);
                    }),
                    'pagination' => [
                        'current_page' => $comments->currentPage(),
                        'last_page' => $comments->lastPage(),
                        'per_page' => $comments->perPage(),
                        'total' => $comments->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve comments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated shares for a post.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getShares(Request $request, $id)
    {
        try {
            $post = V2Post::where('id', $id)->where('status', 'active')->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);

            $shares = V2EngagementShare::with(['user:id,first_name,last_name,username,profile'])
                ->where('post_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success' => true,
                'data' => [
                    'shares' => $shares->map(function ($share) {
                        return $this->formatShareResponse($share);
                    }),
                    'pagination' => [
                        'current_page' => $shares->currentPage(),
                        'last_page' => $shares->lastPage(),
                        'per_page' => $shares->perPage(),
                        'total' => $shares->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve shares.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Like a post.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function like(Request $request, $id)
    {
        try {
            $user = $request->user();

            $post = V2Post::where('id', $id)->where('status', 'active')->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'liked_by_type' => 'required|in:parent,pet',
                'liked_by_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Validate ownership
            $ownershipValidation = ValidationService::validatePostedBy(
                $user->id,
                $validated['liked_by_type'],
                $validated['liked_by_id']
            );

            if (!$ownershipValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $ownershipValidation['message']
                ], 403);
            }

            // Check if already liked
            $existingLike = V2EngagementLike::where('post_id', $id)
                ->where('liked_by_type', $validated['liked_by_type'])
                ->where('liked_by_id', $validated['liked_by_id'])
                ->first();

            if ($existingLike) {
                // Unlike
                $existingLike->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Post unliked successfully',
                    'data' => ['liked' => false]
                ]);
            }

            // Create like
            $like = V2EngagementLike::create([
                'post_id' => $id,
                'parent_id' => $user->id,
                'liked_by_type' => $validated['liked_by_type'],
                'liked_by_id' => $validated['liked_by_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post liked successfully',
                'data' => [
                    'liked' => true,
                    'like' => $this->formatLikeResponse($like)
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to like post.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Share a post.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function share(Request $request, $id)
    {
        try {
            $user = $request->user();

            $post = V2Post::where('id', $id)->where('status', 'active')->first();

            if (!$post) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'shared_by_type' => 'required|in:parent,pet',
                'shared_by_id' => 'required|integer',
                'platform' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Validate ownership
            $ownershipValidation = ValidationService::validatePostedBy(
                $user->id,
                $validated['shared_by_type'],
                $validated['shared_by_id']
            );

            if (!$ownershipValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $ownershipValidation['message']
                ], 403);
            }

            // Create share
            $share = V2EngagementShare::create([
                'post_id' => $id,
                'parent_id' => $user->id,
                'shared_by_type' => $validated['shared_by_type'],
                'shared_by_id' => $validated['shared_by_id'],
                'platform' => $validated['platform'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post shared successfully',
                'data' => $this->formatShareResponse($share)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to share post.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format like response.
     *
     * @param V2EngagementLike $like
     * @return array
     */
    protected function formatLikeResponse($like): array
    {
        return [
            'id' => $like->id,
            'post_id' => $like->post_id,
            'user_id' => $like->user_id,
            'liked_by_type' => $like->liked_by_type,
            'liked_by_id' => $like->liked_by_id,
            'user' => $like->user ? [
                'id' => $like->user->id,
                'name' => $like->user->first_name . ' ' . $like->user->last_name,
                'username' => $like->user->username,
                'profile' => $like->user->profile,
            ] : null,
            'created_at' => $like->created_at,
        ];
    }
    protected function formatRepostResponse($data): array
    {
        return [
            'id' => $data->id,
            'info' => $data->user ? [
                'id' => $data->user->id,
                'name' => $data->user->first_name . ' ' . $data->user->last_name,
                'username' => $data->user->username,
                'profile' => $data->user->profile,
            ] : null,
            'created_at' => $data->created_at,
        ];
    }

    /**
     * Format comment response.
     *
     * @param $comment
     * @return array
     */
    protected function formatCommentResponse($comment): array
    {
        return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'user_id' => $comment->user_id,
            'commented_by_type' => $comment->commented_by_type,
            'commented_by_id' => $comment->commented_by_id,
            'comment' => $comment->comment,
            'user' => $comment->user ? [
                'id' => $comment->user->id,
                'name' => $comment->user->first_name . ' ' . $comment->user->last_name,
                'username' => $comment->user->username,
                'profile' => $comment->user->profile,
            ] : null,
            'created_at' => $comment->created_at,
        ];
    }

    /**
     * Format share response.
     *
     * @param V2EngagementShare $share
     * @return array
     */
    protected function formatShareResponse($share): array
    {
        return [
            'id' => $share->id,
            'post_id' => $share->post_id,
            'user_id' => $share->user_id,
            'shared_by_type' => $share->shared_by_type,
            'shared_by_id' => $share->shared_by_id,
            'platform' => $share->platform,
            'user' => $share->user ? [
                'id' => $share->user->id,
                'name' => $share->user->first_name . ' ' . $share->user->last_name,
                'username' => $share->user->username,
                'profile' => $share->user->profile,
            ] : null,
            'created_at' => $share->created_at,
        ];
    }
}
