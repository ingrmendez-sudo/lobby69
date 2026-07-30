<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.only'            => \App\Http\Middleware\AdminOnly::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
            'profile.completed'     => \App\Http\Middleware\ProfileCompleted::class,
            'check.membership'      => \App\Http\Middleware\CheckMembershipStatus::class,
            'track.seen' => \App\Http\Middleware\TrackLastSeen::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();



// Scheduler: limpiar sesiones de video huerfanas
$app->booted(function () {
    $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
    $schedule->command('video:clean-orphans')->everyMinute();
});
