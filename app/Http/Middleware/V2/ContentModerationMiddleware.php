<?php

namespace App\Http\Middleware\V2;

use App\Services\V2\ContentModerationService;
use App\Models\V2\V2ModerationLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentModerationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $contentType = 'post'): Response
    {
        // Get content from request based on content type
        $content = $this->extractContent($request, $contentType);
        
        if (empty($content)) {
            return $next($request);
        }

        // Run moderation check
        $moderationResult = ContentModerationService::checkContent($content);

        // If content is blocked, reject the request
        if ($moderationResult['action'] === 'blocked') {
            // Log the moderation action
            $this->logModeration($request, $contentType, $content, $moderationResult);

            return response()->json([
                'success' => false,
                'message' => 'Your content violates our community guidelines and cannot be posted.',
                'data' => [
                    'violation_type' => $moderationResult['violation_type'],
                    'reason' => 'Content contains prohibited material.'
                ]
            ], 422);
        }

        // If content is flagged, allow but log for review
        if ($moderationResult['action'] === 'flagged') {
            $this->logModeration($request, $contentType, $content, $moderationResult);
            
            // Add moderation info to request for controller to handle
            $request->attributes->set('moderation_flagged', true);
            $request->attributes->set('moderation_violation_type', $moderationResult['violation_type']);
            $request->attributes->set('moderation_keywords', $moderationResult['matched_keywords']);
        }

        return $next($request);
    }

    /**
     * Extract content from request based on content type.
     *
     * @param Request $request
     * @param string $contentType
     * @return string
     */
    protected function extractContent(Request $request, string $contentType): string
    {
        return match($contentType) {
            'post' => $request->input('content', ''),
            'comment' => $request->input('comment', ''),
            'repost' => $request->input('repost_comment', ''),
            default => $request->input('content', ''),
        };
    }

    /**
     * Log moderation action.
     *
     * @param Request $request
     * @param string $contentType
     * @param string $content
     * @param array $moderationResult
     * @return void
     */
    protected function logModeration(Request $request, string $contentType, string $content, array $moderationResult): void
    {
        $user = $request->user();
        
        if (!$user) {
            return;
        }

        V2ModerationLog::create([
            'user_id' => $user->id,
            'content_type' => $contentType,
            'content_id' => null, // Will be updated after content is created if needed
            'content_text' => $content,
            'violation_type' => $moderationResult['violation_type'],
            'matched_keywords' => $moderationResult['matched_keywords'],
            'action' => $moderationResult['action'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
