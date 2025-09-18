<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Karyawan extends Model
{
    protected $fillable = ['nama', 'nip'];

    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function absensiHariIni(): HasOne
    {
        return $this->hasOne(Absensi::class)
                   ->whereDate('tanggal', today());
    }

    // Accessor untuk status hari ini
    public function getStatusHariIniAttribute(): ?string
    {
        $absensi = $this->absensiHariIni;
        return $absensi ? $absensi->status : null;
    }

    public function getAbsen(): bool
    {
        return $this->absensiHariIni()->exists();
    }

    public function getAbsensiBulanIni()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        return $this->absensis()
                   ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                   ->get();
    }
}