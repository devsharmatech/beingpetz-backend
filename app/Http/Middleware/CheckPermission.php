<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('admin.login');
        }
        
        // Check if user has permission
        if ($user->hasPermission($permission)) {
            return $next($request);
        }
        
        // If no permission, redirect to dashboard with error
        return redirect()->route('admin.dashboard')
            ->with('error', 'You do not have permission to access this page.');
    }
}