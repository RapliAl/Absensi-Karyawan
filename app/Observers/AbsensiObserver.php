<?php

namespace App\Observers;

use App\Models\Absensi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AbsensiObserver
{
    /**
     * Handle the Absensi "created" event.
     */
    public function created(Absensi $absensi): void
    {
        $this->clearWidgetCache($absensi);
        Log::info("AbsensiObserver: Cache cleared after creating absensi for karyawan {$absensi->karyawan_id}");
    }

    /**
     * Handle the Absensi "updated" event.
     */
    public function updated(Absensi $absensi): void
    {
        $this->clearWidgetCache($absensi);
        Log::info("AbsensiObserver: Cache cleared after updating absensi ID {$absensi->id}");
    }

    /**
     * Handle the Absensi "deleted" event.
     */
    public function deleted(Absensi $absensi): void
    {
        $this->clearWidgetCache($absensi);
        Log::info("AbsensiObserver: Cache cleared after deleting absensi ID {$absensi->id}");
    }

    /**
     * Clear widget cache untuk tanggal terkait
     */
    private function clearWidgetCache(Absensi $absensi): void
    {
        $tanggal = $absensi->tanggal instanceof \Carbon\Carbon 
            ? $absensi->tanggal->toDateString() 
            : \Carbon\Carbon::parse($absensi->tanggal)->toDateString();
            
        $cacheKey = "dashboard_absensi_stats_{$tanggal}";
        
        Cache::forget($cacheKey);
        
        // Clear juga untuk hari ini dan kemarin untuk memastikan
        $today = \Carbon\Carbon::now()->toDateString();
        $yesterday = \Carbon\Carbon::now()->subDay()->toDateString();
        
        Cache::forget("dashboard_absensi_stats_{$today}");
        Cache::forget("dashboard_absensi_stats_{$yesterday}");
    }
}