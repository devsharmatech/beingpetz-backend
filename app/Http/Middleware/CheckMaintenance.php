<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenance = SettingsHelper::get('maintenance_mode', '0') == '1';

        if ($isMaintenance) {
            // Allow admin panel and settings API always
            if ($request->is('admin') || $request->is('admin/*') || $request->is('api/v1/cms/settings')) {
                return $next($request);
            }

            // For API requests, return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'maintenance' => true,
                    'message' => 'The application is currently under maintenance. Please try again later.'
                ], 503);
            }

            // For web requests, show a maintenance view if it exists, else default 503
            return response()->view('errors.maintenance', [], 503);
        }

        return $next($request);
    }
}
