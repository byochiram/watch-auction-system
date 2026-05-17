<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\ProcessEndedAuctions;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'can-bid' => \App\Http\Middleware\EnsureUserCanBid::class,
            'throttle.bid' => \App\Http\Middleware\ThrottleBidRequests::class,
        ]);

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );
    })

    ->withCommands([
        ProcessEndedAuctions::class,
        ExpirePendingPayments::class,
    ])

    ->withSchedule(function (Schedule $schedule) {
        // proses lelang selesai (set winner + buat invoice)
        $schedule->command('auctions:process-ended')->everyMinute();

        // proses payment kadaluarsa + suspend bidder 7 hari
        $schedule->command('payments:expire-pending')->everyMinute();

        // proses kirim notif mendekati expire payment
        $schedule->command('payments:send-reminders')->everyMinute();
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
