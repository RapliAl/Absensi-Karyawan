<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapBulanan extends Model
{
    protected $fillable = [
        'bulan', 
        'tahun',
        'nama_bulan',
        'total_karyawan',
        'total_hadir',
        'total_sakit',
        'total_izin',
        'total_hari_kerja',
        'status',
        'file_export',
    ];

    public function getPeriodeAttribute()
    {
        return $this->nama_bulan . ' ' . $this->tahun;
    }

    public function getPersentasiKehadiranAttribute(): float
    {
        $totalExpected = $this->total_karyawan * $this->total_hari_kerja;
        return $totalExpected > 0 ? round(($this->total_hadir / $totalExpected) * 100, 2) : 0;
    }
}
