<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\SkipNgrokWarning;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->web(append: [
            \App\Http\Middleware\SkipNgrokWarning::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'is.admin' => \App\Http\Middleware\IsAdmin::class,
            'plan.selected' => \App\Http\Middleware\EnsurePlanIsSelected::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php' 
    )
    ->withSchedule(function (Schedule $schedule) { // <-- AÑADE ESTE BLOQUE
        $schedule->command('payments:process-due')->daily();
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
