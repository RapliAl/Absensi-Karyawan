<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function(Schedule $schedule) {
        $schedule->call(function () {
            $rekapService = new \App\Services\RekapService();
            $lastMonth = \Carbon\Carbon::now()->subMonth();
            $rekapService->generateRekapBulanan($lastMonth->month, $lastMonth->year);
        })->monthlyOn(1, '08:00')->name('generate_rekap_bulanan');
    })
    ->create();