<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\GenerateMonthlyRecap::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('absensi:rekap-bulanan --last')
                ->monthlyOn(1, '08:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}