<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register custom middleware aliases
        $middleware->alias([
            'cache.response' => \App\Http\Middleware\CacheResponse::class,
            'cache.headers' => \App\Http\Middleware\SetCacheHeaders::class,
            'prevent.back' => \App\Http\Middleware\PreventBackHistory::class,
            'ensure.auth' => \App\Http\Middleware\EnsureAuthenticated::class,
            // Session security middleware (Option C implementation)
            'validate.session' => \App\Http\Middleware\ValidateSessionToken::class,
            'enforce.guard' => \App\Http\Middleware\EnforceGuardRouteBinding::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
