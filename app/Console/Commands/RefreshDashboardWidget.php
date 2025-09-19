<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RefreshDashboardWidget extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:refresh-widget';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh dashboard widget absensi setiap jam 8 pagi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai refresh dashboard widget...');
        
        try {
            // Clear cache untuk dashboard widget
            $dates = [
                Carbon::now()->toDateString(),
                Carbon::now()->subDay()->toDateString(),
                Carbon::now()->addDay()->toDateString()
            ];
            
            foreach ($dates as $date) {
                $cacheKey = "dashboard_absensi_stats_{$date}";
                Cache::forget($cacheKey);
                $this->line("Cache cleared untuk tanggal: {$date}");
            }
            
            // Log refresh time
            Log::info('Dashboard widget di-refresh pada: ' . Carbon::now()->toDateTimeString());
            
            $this->info('Dashboard widget berhasil di-refresh pada: ' . Carbon::now()->format('Y-m-d H:i:s'));
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error saat refresh dashboard widget: ' . $e->getMessage());
            Log::error('Dashboard widget refresh error: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
