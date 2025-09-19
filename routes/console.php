<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use App\Services\RekapService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('absensi:rekap-bulanan {--month=} {--year=} {--current} {--last}', function(){
    $rekapService = new RekapService();

    if ($this->option('last')) {
        $month = Carbon::now()->subMonth()->month;
        $year = Carbon::now()->subMonth()->year;
    } elseif ($this->option('current')) {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
    } else {
        $month = $this->option('month') ?? Carbon::now()->month;
        $year = $this->option('year') ?? Carbon::now()->year;
    }

    $rekap = $rekapService->generateRekapBulanan($month, $year);

})->purpose('Generate rekap absensi bulanan');

// Schedule untuk refresh dashboard widget setiap jam 8 pagi
app(Schedule::class)->command('dashboard:refresh-widget')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta')
    ->description('Refresh dashboard widget absensi setiap jam 8 pagi');