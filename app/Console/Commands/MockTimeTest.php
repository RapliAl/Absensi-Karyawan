<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Cache;

class MockTimeTest extends Command
{
    protected $signature = 'test:mock-time {time} {--karyawan-id=} {--clear-cache} {--show-cache}';
    protected $description = 'Mock specific time and test complete system behavior';

    public function handle()
    {
        $mockTime = $this->argument('time');
        
        try {
            $mockedNow = Carbon::today()->setTimeFromTimeString($mockTime);
        } catch (\Exception $e) {
            $this->error("❌ Invalid time format. Use HH:MM (e.g., 08:00)");
            return 1;
        }
        
        $this->info("🕐 TIME TRAVEL TO: {$mockedNow->format('H:i')} ({$mockedNow->format('d M Y')})");
        $this->newLine();
        
        // Clear cache jika diminta
        if ($this->option('clear-cache')) {
            Cache::flush();
            $this->line('🗑️  All cache cleared');
            $this->newLine();
        }
        
        // Get test karyawan
        $karyawan = $this->getTestKaryawan();
        
        if (!$karyawan) {
            $this->error('❌ No karyawan data for testing');
            return 1;
        }
        
        // Run comprehensive tests
        $this->testWidgetBehavior($mockedNow);
        $this->testInterfaceBehavior($mockedNow, $karyawan);
        $this->testCacheBehavior($mockedNow);
        
        if ($this->option('show-cache')) {
            $this->showCacheDetails($mockedNow);
        }
        
        $this->showSystemSummary($mockedNow, $karyawan);
        
        return 0;
    }
    
    private function getTestKaryawan()
    {
        $karyawanId = $this->option('karyawan-id');
        
        if ($karyawanId) {
            return Karyawan::find($karyawanId);
        }
        
        return Karyawan::first();
    }
    
    private function testWidgetBehavior(Carbon $mockedNow)
    {
        $this->info('📊 WIDGET BEHAVIOR TEST:');
        
        // Simulasi logika widget
        $workingDate = $mockedNow->hour >= 8 
            ? $mockedNow->toDateString() 
            : $mockedNow->copy()->subDay()->toDateString();
        
        $cacheKey = "dashboard_absensi_stats_{$workingDate}";
        
        // Hitung stats seperti di widget asli
        $hadirCount = Absensi::where('status', 'Hadir')
            ->whereDate('tanggal', $workingDate)
            ->count();
        
        $sakitCount = Absensi::where('status', 'Sakit')
            ->whereDate('tanggal', $workingDate)
            ->count();
        
        $izinCount = Absensi::where('status', 'Izin')
            ->whereDate('tanggal', $workingDate)
            ->count();
        
        $totalKaryawan = Karyawan::count();
        $alfaCount = max(0, $totalKaryawan - ($hadirCount + $sakitCount + $izinCount));
        
        $persentase = $totalKaryawan > 0 ? round(($hadirCount / $totalKaryawan) * 100, 1) : 0;
        
        // Tentukan polling interval
        $pollingInterval = $this->getPollingInterval($mockedNow);
        
        $this->line("├── Working Date: {$workingDate}");
        $this->line("├── Cache Key: {$cacheKey}");
        $this->line("├── Hadir: {$hadirCount}, Sakit: {$sakitCount}, Izin: {$izinCount}, Alfa: {$alfaCount}");
        $this->line("├── Persentase Kehadiran: {$persentase}%");
        $this->line("├── Polling Interval: {$pollingInterval}");
        $this->line("└── Period: " . ($mockedNow->hour >= 8 ? 'HARI INI' : 'KEMARIN'));
        $this->newLine();
    }
    
    private function testInterfaceBehavior(Carbon $mockedNow, Karyawan $karyawan)
    {
        $this->info("🖥️  INTERFACE BEHAVIOR TEST ({$karyawan->nama}):");
        
        // Simulasi logika interface
        $workingDate = $mockedNow->hour >= 8 
            ? $mockedNow->toDateString() 
            : $mockedNow->copy()->subDay()->toDateString();
        
        $absensi = $karyawan->absensis()
            ->whereDate('tanggal', $workingDate)
            ->first();
        
        $status = $absensi ? $absensi->status : 'Belum Absen';
        $sudahAbsen = $absensi ? true : false;
        
        // Simulasi actions visibility
        $actionsVisible = $sudahAbsen ? ['Reset'] : ['Hadir', 'Sakit', 'Izin'];
        
        // Simulasi badge color
        $badgeColor = match($status) {
            'Hadir' => 'success',
            'Sakit' => 'warning', 
            'Izin' => 'info',
            default => 'danger'
        };
        
        $this->line("├── Working Date: {$workingDate}");
        $this->line("├── Status Display: {$status}");
        $this->line("├── Badge Color: {$badgeColor}");
        $this->line("├── Actions Available: " . implode(', ', $actionsVisible));
        $this->line("├── Sudah Absen: " . ($sudahAbsen ? 'Ya' : 'Tidak'));
        $this->line("└── Period: " . ($mockedNow->hour >= 8 ? 'HARI INI' : 'KEMARIN'));
        $this->newLine();
    }
    
    private function testCacheBehavior(Carbon $mockedNow)
    {
        $this->info('💾 CACHE BEHAVIOR TEST:');
        
        $workingDate = $mockedNow->hour >= 8 
            ? $mockedNow->toDateString() 
            : $mockedNow->copy()->subDay()->toDateString();
        
        $cacheKey = "dashboard_absensi_stats_{$workingDate}";
        $cacheExists = Cache::has($cacheKey);
        
        // Test cache duration logic
        $cacheDuration = $this->getCacheDuration($mockedNow);
        
        $this->line("├── Primary Cache Key: {$cacheKey}");
        $this->line("├── Cache Exists: " . ($cacheExists ? 'YES' : 'NO'));
        $this->line("├── Cache Duration: {$cacheDuration}");
        $this->line("└── Should Clear at 08:00: " . ($mockedNow->hour === 8 ? 'YES' : 'NO'));
        $this->newLine();
    }
    
    private function showCacheDetails(Carbon $mockedNow)
    {
        $this->info('🔍 DETAILED CACHE ANALYSIS:');
        
        $yesterday = $mockedNow->copy()->subDay()->toDateString();
        $today = $mockedNow->toDateString();
        
        $cacheKeys = [
            "dashboard_absensi_stats_{$yesterday}" => 'Yesterday Cache',
            "dashboard_absensi_stats_{$today}" => 'Today Cache',
            'last_cache_refresh' => 'Last Refresh Timestamp'
        ];
        
        foreach ($cacheKeys as $key => $description) {
            $exists = Cache::has($key);
            $value = $exists ? Cache::get($key) : 'Not Found';
            
            if (is_array($value)) {
                $value = 'Array[' . count($value) . ']';
            }
            
            $this->line("├── {$description}: " . ($exists ? "✅ {$value}" : "❌ Empty"));
        }
        
        $this->newLine();
    }
    
    private function showSystemSummary(Carbon $mockedNow, Karyawan $karyawan)
    {
        $isAfterReset = $mockedNow->hour >= 8;
        $workingDate = $isAfterReset ? $mockedNow->toDateString() : $mockedNow->copy()->subDay()->toDateString();
        
        $this->info('📋 SYSTEM SUMMARY:');
        $this->line("🕐 Simulated Time: {$mockedNow->format('H:i')}");
        $this->line("📅 Working Date: " . Carbon::parse($workingDate)->format('d M Y'));
        $this->line("🔄 Reset Status: " . ($isAfterReset ? '✅ AFTER RESET' : '⏳ BEFORE RESET'));
        $this->line("👤 Test Karyawan: {$karyawan->nama}");
        
        // Next reset time
        $nextReset = $mockedNow->copy()->startOfDay()->addHours(8);
        if ($mockedNow->hour >= 8) {
            $nextReset->addDay();
        }
        
        $this->line("⏰ Next Reset: {$nextReset->format('d M Y H:i')}");
        $this->line("⚡ System: " . ($isAfterReset ? 'Using TODAY data' : 'Using YESTERDAY data'));
    }
    
    private function getPollingInterval(Carbon $time): string
    {
        if ($time->hour === 8 && $time->minute < 5) {
            return '30s (Intensive)';
        } elseif ($time->hour >= 7 && $time->hour <= 17) {
            return '2m (Normal)';
        } else {
            return '5m (Slow)';
        }
    }
    
    private function getCacheDuration(Carbon $time): string
    {
        if ($time->hour === 8) {
            return '60s (Reset hour)';
        }
        
        return '300s (Normal)';
    }
}