<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Karyawan;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class TestAbsensiWidgetUpdate extends Command
{
    protected $signature = 'test:absensi-widget-update';
    protected $description = 'Test apakah widget ter-update ketika ada data absensi baru';

    public function handle()
    {
        $this->info('🧪 TESTING ABSENSI WIDGET UPDATE');
        $this->newLine();
        
        // Ambil karyawan pertama untuk test
        $karyawan = Karyawan::first();
        
        if (!$karyawan) {
            $this->error('❌ Tidak ada karyawan untuk test!');
            return 1;
        }
        
        $this->info("👤 Testing dengan karyawan: {$karyawan->nama}");
        
        // Tentukan working date
        $now = Carbon::now();
        $workingDate = $now->hour >= 8 ? $now->toDateString() : $now->copy()->subDay()->toDateString();
        $cacheKey = "dashboard_absensi_stats_{$workingDate}";
        
        $this->line("📅 Working date: {$workingDate}");
        $this->line("🔑 Cache key: {$cacheKey}");
        $this->newLine();
        
        // Step 1: Cek status awal
        $this->info('📊 STEP 1: Status awal');
        $this->showCurrentStats($workingDate);
        $this->line("💾 Cache exists: " . (Cache::has($cacheKey) ? 'YES' : 'NO'));
        $this->newLine();
        
        // Step 2: Hapus absensi existing jika ada
        $existing = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', $workingDate)
            ->first();
            
        if ($existing) {
            $this->info('🗑️  STEP 2: Hapus absensi existing');
            $existing->delete();
            $this->line("✅ Absensi existing dihapus");
            sleep(1);
            $this->line("💾 Cache exists after delete: " . (Cache::has($cacheKey) ? 'YES' : 'NO'));
            $this->newLine();
        }
        
        // Step 3: Tambah absensi baru
        $this->info('➕ STEP 3: Tambah absensi HADIR');
        $newAbsensi = Absensi::create([
            'karyawan_id' => $karyawan->id,
            'tanggal' => $workingDate,
            'status' => 'Hadir',
            'jam_absen' => now()->format('H:i:s') // Tambah jam_absen
        ]);
        
        $this->line("✅ Absensi baru ditambahkan (ID: {$newAbsensi->id})");
        sleep(1);
        
        // Step 4: Cek status setelah tambah
        $this->info('📊 STEP 4: Status setelah tambah absensi');
        $this->line("💾 Cache exists after create: " . (Cache::has($cacheKey) ? 'YES' : 'NO'));
        $this->showCurrentStats($workingDate);
        $this->newLine();
        
        // Step 5: Update status absensi
        $this->info('🔄 STEP 5: Update status ke SAKIT');
        $newAbsensi->update(['status' => 'Sakit']);
        $this->line("✅ Status diupdate ke Sakit");
        sleep(1);
        
        // Step 6: Cek status final
        $this->info('📊 STEP 6: Status final');
        $this->line("💾 Cache exists after update: " . (Cache::has($cacheKey) ? 'YES' : 'NO'));
        $this->showCurrentStats($workingDate);
        $this->newLine();
        
        // Cleanup
        $this->info('🧹 CLEANUP: Hapus data test');
        $newAbsensi->delete();
        $this->line("✅ Data test dihapus");
        
        $this->newLine();
        $this->info('✅ TEST COMPLETED!');
        $this->line('🔍 Cek log di storage/logs/laravel.log untuk detail observer dan widget');
        
        return 0;
    }
    
    private function showCurrentStats($workingDate)
    {
        $hadir = Absensi::whereDate('tanggal', $workingDate)->where('status', 'Hadir')->count();
        $sakit = Absensi::whereDate('tanggal', $workingDate)->where('status', 'Sakit')->count();
        $izin = Absensi::whereDate('tanggal', $workingDate)->where('status', 'Izin')->count();
        $total = Absensi::whereDate('tanggal', $workingDate)->count();
        
        $this->table(
            ['Status', 'Count'],
            [
                ['Hadir', $hadir],
                ['Sakit', $sakit], 
                ['Izin', $izin],
                ['Total', $total]
            ]
        );
    }
}