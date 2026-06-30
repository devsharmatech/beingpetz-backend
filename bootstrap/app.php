<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckPermission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

return Application::configure(basePath: dirname(__DIR__))

    // ⭐ Add this line to fix MySQL max key length error
    ->booting(function () {
        Schema::defaultStringLength(191);
    })

    ->withRouting(
        api: __DIR__.'/../routes/api.php',

        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function () {
            Route::middleware(['web'])
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
                
            // V2 API Routes
            Route::middleware(['api'])
                ->prefix('api')
                ->group(base_path('routes/api_v2.php'));
        }
    )

    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(\App\Http\Middleware\CheckMaintenance::class);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'check.permission' => CheckPermission::class,
            'last_active' => \App\Http\Middleware\UpdateLastActiveMiddleware::class,
            'v2.moderation' => \App\Http\Middleware\V2\ContentModerationMiddleware::class,
        ]);
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\UpdateLastActiveMiddleware::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    ->create();
