<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\V2Post;
use App\Models\V2\V2Repost;
use App\Services\V2\ValidationService;
use App\Services\V2\ContentModerationService;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class RepostController extends Controller
{
    /**
     * Store a new repost (V2).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'post_id' => 'required|integer|exists:posts,id',
                'is_public' => 'nullable|boolean',
                'repost_comment' => 'nullable|string|max:2000',
                'reposted_by_type' => 'required|in:parent,pet',
                'pet_id' => 'nullable|exists:pets,id',
                'reposted_by_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Validate original post exists and is active
            $originalPost = V2Post::where('id', $validated['post_id'])
                ->where('status', 'active')
                ->first();

            if (!$originalPost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Original post not found or is not active.'
                ], 404);
            }

            // Validate reposted_by ownership
            $ownershipValidation = ValidationService::validatePostedBy(
                $user->id,
                $validated['reposted_by_type'],
                $validated['reposted_by_id']
            );

            if (!$ownershipValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $ownershipValidation['message']
                ], 403);
            }

            // Content moderation check for repost comment (V2: Keywords -> AI -> Logging)
            // if (!empty($validated['repost_comment'])) {
            //     $moderationResult = ContentModerationService::validate(
            //         $validated['repost_comment'],
            //         null,
            //         $user->id,
            //         'repost',
            //         $request->ip(),
            //         $request->userAgent()
            //     );
                
            //     if ($moderationResult['action'] === 'blocked') {
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Your repost comment violates our community guidelines.',
            //             'data' => [
            //                 'violation_type' => $moderationResult['violation_type'],
            //                 'reason' => $moderationResult['reason'] ?? 'Violation detected'
            //             ]
            //         ], 422);
            //     }
            // }

            // Sanitize repost comment
            $sanitized = ValidationService::sanitizeInput([
                'repost_comment' => $validated['repost_comment'] ?? '',
            ]);

            try {
                // Create repost (maps to posts table)
                $repost = V2Repost::create([
                    'repost_id' => $validated['post_id'],
                    'slug' => Str::slug(Str::random(6) . '-' . now()->timestamp),
                    'pet_id' => $validated['pet_id'] ?? null,
                    'parent_id' => $user->id,
                    'is_public' => $validated['is_public'] ?? true,
                    'posted_by_type' => $validated['reposted_by_type'],
                    'posted_by_id' => $validated['reposted_by_id'],
                    'content' => $sanitized['repost_comment'] ?: null,
                    'status' => 'active',
                    'moderation_reason' => null,
                ]);

                // Log if flagged
                if (!empty($validated['repost_comment']) && ($moderationResult['action'] ?? '') === 'flagged') {
                    ContentModerationService::logModeration(
                        $user->id,
                        'repost',
                        $repost->id,
                        $validated['repost_comment'],
                        $moderationResult,
                        $request->ip(),
                        $request->userAgent()
                    );
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Post reposted successfully',
                    'data' => $this->formatRepostResponse($repost)
                ], 201);

            } catch (QueryException $e) {
                if ($e->getCode() == 23000) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already reposted this post.'
                    ], 422);
                }
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to repost.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

public function store(Request $request)
{
    try {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:posts,id',
            'is_public' => 'nullable|boolean',
            'repost_comment' => 'nullable|string|max:2000',
            'reposted_by_type' => 'required|in:parent,pet',
            'pet_id' => 'nullable|exists:pets,id',
            'reposted_by_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // 🔹 Get original post
        $originalPost = V2Post::where('id', $validated['post_id'])
            ->where('status', 'active')
            ->first();

        if (!$originalPost) {
            return response()->json([
                'success' => false,
                'message' => 'Original post not found or is not active.'
            ], 404);
        }

        // 🔹 Ownership validation
        $ownershipValidation = ValidationService::validatePostedBy(
            $user->id,
            $validated['reposted_by_type'],
            $validated['reposted_by_id']
        );

        if (!$ownershipValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $ownershipValidation['message']
            ], 403);
        }

        // 🔹 Sanitize input
        $sanitized = ValidationService::sanitizeInput([
            'repost_comment' => $validated['repost_comment'] ?? '',
        ]);

        try {
            
            // 🔹 Create repost
            $repost = V2Repost::create([
                'repost_id' => $validated['post_id'],
                'slug' => Str::slug(Str::random(6) . '-' . now()->timestamp),
                'pet_id' => $validated['pet_id'] ?? null,
                'parent_id' => $user->id,
                'is_public' => $validated['is_public'] ?? true,
                'posted_by_type' => $validated['reposted_by_type'],
                'posted_by_id' => $validated['reposted_by_id'],
                'content' => $sanitized['repost_comment'] ?: null,
                'status' => 'active',
                'moderation_reason' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 🔔 NOTIFICATION SECTION (NEW ADDED)
            |--------------------------------------------------------------------------
            */

            $senderName = $user->first_name ?? 'Someone';

            // ✅ Notify ORIGINAL OWNER
            if ($originalPost->parent_id != $user->id) {

                $notification = Notification::create([
                    'user_id'       => $originalPost->parent_id,
                    'sender_id'     => $user->id,
                    'notifiable_id' => $repost->id,
                    'type'          => 'repost',
                    'title'         => 'Your post was shared',
                    'message'       => $senderName . ' reposted your post',
                    'is_read'       => false,
                ]);

                // 🔹 Send push
                $originalUser = User::select('id', 'device_token')
                    ->find($originalPost->parent_id);

                if ($originalUser && $originalUser->device_token) {
                    try {
                        $this->fcm->sendNotification(
                            [$originalUser->device_token],
                            [
                                'title' => 'Your post was shared',
                                'body'  => $senderName . ' reposted your post',
                                'type'  => 'repost',
                                'sender_id' => (string) $user->id,
                                'notification_id' => (string) $notification->id,
                                'notifiable_id' => (string) $repost->id
                            ]
                        );
                    } catch (\Exception $e) {
                        \Log::error("Repost notification failed: " . $e->getMessage());
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | ✅ RESPONSE
            |--------------------------------------------------------------------------
            */
            
            return response()->json([
                'success' => true,
                'message' => 'Post reposted successfully',
                'data' => $repost
            ], 201);

        } catch (QueryException $e) {

            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reposted this post.'
                ], 422);
            }

            throw $e;
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to repost.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Display the specified repost.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $repost = V2Repost::with('originalPost')->find($id);

            if (!$repost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Repost not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatRepostResponse($repost, true)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve repost.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified repost.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $repost = V2Repost::find($id);

            if (!$repost) {
                return response()->json([
                    'success' => false,
                    'message' => 'Repost not found'
                ], 404);
            }

            // Check ownership
            if ($repost->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this repost.'
                ], 403);
            }

            $repost->delete();

            return response()->json([
                'success' => true,
                'message' => 'Repost deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete repost.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format repost response.
     *
     * @param V2Repost $repost
     * @param bool $includeOriginalPost
     * @return array
     */
    protected function formatRepostResponse(V2Repost $repost, bool $includeOriginalPost = false): array
    {
        $data = [
            'id' => $repost->id,
            'original_post_id' => $repost->original_post_id,
            'user_id' => $repost->user_id,
            'reposted_by_type' => $repost->reposted_by_type,
            'reposted_by_id' => $repost->reposted_by_id,
            'repost_comment' => $repost->repost_comment,
            'created_at' => $repost->created_at,
        ];

        if ($includeOriginalPost && $repost->originalPost) {
            $data['original_post'] = [
                'id' => $repost->originalPost->id,
                'posted_by_type' => $repost->originalPost->posted_by_type,
                'posted_by_id' => $repost->originalPost->posted_by_id,
                'content' => $repost->originalPost->content,
                'media_urls' => $repost->originalPost->media_urls,
                'created_at' => $repost->originalPost->created_at,
            ];
        }

        return $data;
    }
}
