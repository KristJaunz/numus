<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(\App\Jobs\CreateJumisCache::class)->dailyAt('7:00')->withoutOverlapping();
        $schedule->job(\App\Jobs\SyncTender::class)->dailyAt('22:00')->withoutOverlapping();
       // $schedule->job(\App\Jobs\ConfirmDocuments::class)->dailyAt('23:00')->withoutOverlapping();

    })
    ->create();
