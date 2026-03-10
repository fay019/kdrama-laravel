<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\CheckSetup::class);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'check.password' => \App\Http\Middleware\CheckPasswordMustChange::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Le rafraîchissement se fait désormais à la consultation (24h cache)
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
