<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActiveMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = auth()->user();
        
        // Fallback for API calls that pass IDs in request but might not be authenticated via standard session/token
        if (!$user) {
            $userId = $request->input('user_id') ?? $request->input('parent_id') ?? $request->input('admin_id');
            if ($userId) {
                $user = \App\Models\User::find($userId);
            }
        }

        if ($user) {
            $action = $this->getActionDetails($request);
            
            if ($action) {
                // Update activity details
                \DB::table('users')->where('id', $user->id)->update([
                    'last_active_at' => now(),
                    'last_activity_details' => $action,
                    'last_login' => now(), // Keep last_login updated on any activity
                    'updated_at' => now(),
                ]);
            }
        }

        return $response;
    }

    /**
     * Determine user activity details based on request
     */
    private function getActionDetails(Request $request)
    {
        $uri = $request->path();
        $method = strtoupper($request->method());

        // Define activity mapping
        $activities = [
            'POST|api/v1/post/create' => 'Created a new post',
            'POST|api/v1/post/re-post' => 'Shared a post',
            'POST|api/v1/post/like' => 'Liked a post',
            'POST|api/v1/post/share' => 'Shared a post link',
            'POST|api/v1/post/comment' => 'Commented on a post',
            'POST|api/v1/pet/add' => 'Added a new pet',
            'POST|api/v1/pet/update' => 'Updated pet details',
            'POST|api/v1/auth/update-profile' => 'Updated profile',
            'POST|api/v1/pet/community/join' => 'Joined a community',
            'POST|api/v1/community/send-message' => 'Sent a message in community',
            'POST|api/v1/friend/send-message' => 'Sent a private message',
            'POST|api/v1/pet/lost-found/store' => 'Reported a lost/found pet',
            'POST|api/v1/pet/create-adoption' => 'Created an adoption listing',
            'POST|api/v1/vaccine/save-records' => 'Updated vaccine records',
            'POST|api/v1/pet/friends/send-request' => 'Sent a friend request',
            'POST|api/v1/auth/login' => 'Logged in',
            'POST|api/v1/auth/login-verify' => 'Logged in (OTP verification)',
            'POST|api/v1/auth/register-verify' => 'Completed registration',
        ];

        // Check for direct matches
        $key = "{$method}|{$uri}";
        if (isset($activities[$key])) {
            return $activities[$key];
        }

        // Check for partial matches or dynamic routes if needed
        foreach ($activities as $pattern => $description) {
            if (strpos($pattern, '*') !== false) {
                $regex = str_replace(['POST|', 'GET|', '*', '/'], ['', '', '.*', '\/'], $pattern);
                if (preg_match("/^{$regex}$/", $uri)) {
                    return $description;
                }
            }
        }

        // Default: If it's a POST/PUT/DELETE request, consider it an activity even if not mapped specifically
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            return 'Performed an action';
        }

        return null;
    }
}
