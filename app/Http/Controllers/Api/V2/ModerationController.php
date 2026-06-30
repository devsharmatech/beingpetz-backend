<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Services\V2\ContentModerationService;
use App\Services\V2\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModerationController extends Controller
{
    /**
     * Check content for moderation violations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function check(Request $request)
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'content' => 'required_without:image|nullable|string|max:5000',
                'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5120',
                'media_url' => 'nullable|url|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Sanitize content if provided
            $content = $validated['content'] ?? '';
            if (!empty($content)) {
                $sanitized = ValidationService::sanitizeInput(['content' => $content]);
                $content = $sanitized['content'];
            }

            // Call unified moderation service (Keyword + AI + Logging)
            $moderationResult = ContentModerationService::validate(
                $content,
                $validated['media_url'] ?? null,
                $user ? $user->id : 0,
                'pre_check',
                $request->ip(),
                $request->userAgent(),
                $request->file('image')
            );

            return response()->json([
                'is_allowed' => $moderationResult['action'] === 'allowed',
                'reason' => $moderationResult['reason'] ?? 'Content is safe',
                'violation_type' => $moderationResult['violation_type'] ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Moderation check failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
