<?php

namespace App\Filament\Widgets;

use App\Models\Absensi;
use App\Models\Karyawan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardKaryawanHadirWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';
    
    protected int | string | array $columnSpan = 'full';
    
    // Cache dengan auto-invalidation via Observer
    protected function getStats(): array
    {
        // Debug log
        \Illuminate\Support\Facades\Log::info('DashboardWidget: getStats() called at ' . now());
        
        // Get current time and target date
        $today = Carbon::now();
        $targetDate = $today->hour >= 8 ? $today->toDateString() : $today->subDay()->toDateString();
        $cacheKey = "dashboard_absensi_stats_{$targetDate}";
        
        // Cache selama 2 menit (akan auto-clear via Observer saat ada perubahan data)
        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($targetDate) {
            \Illuminate\Support\Facades\Log::info("DashboardWidget: Generating fresh data for {$targetDate}");
            
            // Query data
            $totalKaryawan = Karyawan::count();
            
            // Hitung karyawan yang hadir hari ini (status 'Hadir' dengan huruf besar)
            $karyawanHadir = Absensi::whereDate('tanggal', $targetDate)
                ->where('status', 'Hadir')
                ->count();
                
            // Hitung karyawan yang sakit
            $karyawanSakit = Absensi::whereDate('tanggal', $targetDate)
                ->where('status', 'Sakit')
                ->count();
                
            // Hitung karyawan yang izin
            $karyawanIzin = Absensi::whereDate('tanggal', $targetDate)
                ->where('status', 'Izin')
                ->count();
                
            // Hitung karyawan yang alfa (tidak ada record absensi)
            $karyawanAbsen = Absensi::whereDate('tanggal', $targetDate)->count();
            $karyawanAlfa = $totalKaryawan - $karyawanAbsen;
            
            // Hitung persentase kehadiran
            $persentaseKehadiran = $totalKaryawan > 0 ? round(($karyawanHadir / $totalKaryawan) * 100, 1) : 0;
            
            // Debug log hasil
            \Illuminate\Support\Facades\Log::info("DashboardWidget: Hadir={$karyawanHadir}, Sakit={$karyawanSakit}, Izin={$karyawanIzin}, Alfa={$karyawanAlfa}, Persentase={$persentaseKehadiran}%");
            
            // Data chart untuk 7 hari terakhir
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->toDateString();
                $count = Absensi::whereDate('tanggal', $date)->where('status', 'Hadir')->count();
                $chartData[] = $count;
            }
            
            return [
                Stat::make('Karyawan Hadir Hari Ini', $karyawanHadir)
                    ->description('Total: ' . $totalKaryawan . ' karyawan')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color($persentaseKehadiran >= 80 ? 'success' : ($persentaseKehadiran >= 60 ? 'warning' : 'danger'))
                    ->chart($chartData),
                    
                Stat::make('Persentase Kehadiran', $persentaseKehadiran . '%')
                    ->description('Tingkat kehadiran')
                    ->descriptionIcon('heroicon-m-chart-bar')
                    ->color($persentaseKehadiran >= 80 ? 'success' : ($persentaseKehadiran >= 60 ? 'warning' : 'danger')),
                    
                Stat::make('Sakit', $karyawanSakit)
                    ->description('Karyawan sakit')
                    ->descriptionIcon('heroicon-m-heart')
                    ->color('warning'),
                    
                Stat::make('Izin', $karyawanIzin)
                    ->description('Karyawan izin')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('info'),
                    
                Stat::make('Alfa', $karyawanAlfa)
                    ->description('Tidak ada keterangan')
                    ->descriptionIcon('heroicon-m-x-circle')
                    ->color('danger'),
            ];
        });
    }
    
    protected function getPollingInterval(): ?string
    {
        // Untuk debugging: polling sangat agresif
        return '5s';
    }
}